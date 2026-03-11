<?php
namespace App\Auth\Providers;

use App\Auth\Contracts\AuthProvider;
use App\Core\Database;

class LdapProvider implements AuthProvider
{
    // Default search filters per user type — used when no custom filter is set
    private const PRESETS = [
        'ms_ad'    => ['attr' => 'sAMAccountName', 'filter' => '(&(objectClass=user)(sAMAccountName=%s))'],
        'novell'   => ['attr' => 'cn',             'filter' => '(&(objectClass=inetOrgPerson)(cn=%s))'],
        'posix'    => ['attr' => 'uid',            'filter' => '(&(objectClass=posixAccount)(uid=%s))'],
        'samba'    => ['attr' => 'sAMAccountName', 'filter' => '(&(objectClass=sambaSamAccount)(sAMAccountName=%s))'],
        'inet_org' => ['attr' => 'uid',            'filter' => '(&(objectClass=inetOrgPerson)(uid=%s))'],
        'custom'   => ['attr' => 'uid',            'filter' => '(uid=%s)'],
    ];

    public function getName(): string  { return 'ldap'; }
    public function getLabel(): string { return 'Directory / LDAP'; }

    public function isConfigured(array $settings): bool
    {
        return !empty($settings['ldap_host_url']);
    }

    public function getAuthUrl(array $settings, string $callbackBase, string $state): ?string
    {
        return null; // form-based
    }

    /**
     * Verify LDAP credentials and return directory info without touching the DB.
     * Returns ['dn' => ..., 'name' => ..., 'email' => ..., 'username' => ...] or null.
     */
    public function verifyAndGetInfo(array $credentials, array $settings): ?array
    {
        if (!extension_loaded('ldap')) return null;

        $username = trim($credentials['username'] ?? $credentials['email'] ?? '');
        $password = $credentials['password'] ?? '';

        if (!$username || !$password) return null;

        // ── Connection ───────────────────────────────────────────
        $hostUrl  = trim($settings['ldap_host_url'] ?? '');
        $firstUrl = trim(explode(';', $hostUrl)[0]);
        if (!$firstUrl) return null;

        $conn = @ldap_connect($firstUrl);
        if (!$conn) return null;

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        // ── Service-account bind ─────────────────────────────────
        $bindUser = $settings['ldap_bind_user']     ?? '';
        $bindPass = $settings['ldap_bind_password'] ?? '';
        $bound    = $bindUser
            ? @ldap_bind($conn, $bindUser, $bindPass)
            : @ldap_bind($conn);

        if (!$bound) { ldap_unbind($conn); return null; }

        // ── Build search filter ──────────────────────────────────
        $userType  = $settings['ldap_user_type'] ?? 'ms_ad';
        $preset    = self::PRESETS[$userType] ?? self::PRESETS['ms_ad'];

        $userAttr = $settings['ldap_user_attribute'] ?? '';
        if (!$userAttr) $userAttr = $preset['attr'];

        $filterTpl = $settings['ldap_user_filter'] ?? '';
        if (!$filterTpl) {
            $filterTpl = preg_replace(
                '/\(' . preg_quote($preset['attr'], '/') . '=%s\)/',
                '(' . $userAttr . '=%s)',
                $preset['filter']
            );
        }

        $safeUser = ldap_escape($username, '', LDAP_ESCAPE_FILTER);
        $filter   = sprintf($filterTpl, $safeUser);

        // ── Search contexts ──────────────────────────────────────
        $contexts   = $this->parseContexts($settings);
        // MS Active Directory always needs subtree search (ldap_list is restricted by AD)
        $forceSubtree = ($userType === 'ms_ad');
        $searchSub    = $forceSubtree || !empty($settings['ldap_search_sub']);
        $fetchAttrs   = ['dn', 'mail', 'cn', 'displayname', 'userprincipalname'];

        $userDn    = null;
        $userEmail = null;
        $userName  = null;

        foreach ($contexts as $baseDn) {
            $result = $searchSub
                ? @ldap_search($conn, $baseDn, $filter, $fetchAttrs)
                : @ldap_list($conn, $baseDn, $filter, $fetchAttrs);
            // If one-level search was blocked (e.g. AD), fall back to subtree
            if (!$result && !$searchSub) {
                $result = @ldap_search($conn, $baseDn, $filter, $fetchAttrs);
            }
            if (!$result) continue;

            $entries = ldap_get_entries($conn, $result);
            if ($entries['count'] < 1) continue;

            $entry     = $entries[0];
            $userDn    = $entry['dn'];
            $userEmail = $entry['mail'][0]
                      ?? $entry['userprincipalname'][0]
                      ?? $username;
            $userName  = $entry['displayname'][0]
                      ?? $entry['cn'][0]
                      ?? $username;
            break;
        }

        if (!$userDn) { ldap_unbind($conn); return null; }

        // ── Verify user's own credentials ────────────────────────
        if (!@ldap_bind($conn, $userDn, $password)) {
            ldap_unbind($conn);
            return null;
        }
        ldap_unbind($conn);

        return [
            'dn'       => $userDn,
            'name'     => $userName,
            'email'    => $userEmail,
            'username' => $username,
        ];
    }

    /**
     * Full authentication: verify LDAP credentials then find/provision user in DB.
     */
    public function authenticate(array $credentials, array $settings, string $callbackBase): ?array
    {
        $orgId = (int)($credentials['org_id'] ?? 0);
        if (!$orgId) return null;

        $info = $this->verifyAndGetInfo($credentials, $settings);
        if (!$info) return null;

        $userDn    = $info['dn'];
        $userEmail = $info['email'];
        $userName  = $info['name'];

        // ── Find or provision user in our DB ─────────────────────
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT * FROM users
             WHERE organization_id = ? AND (ldap_dn = ? OR email = ?) AND active = 1"
        );
        $stmt->execute([$orgId, $userDn, $userEmail]);
        $user = $stmt->fetch();

        if (!$user) {
            // Auto-provision as staff on first successful LDAP login
            $db->prepare(
                "INSERT INTO users (organization_id, name, email, role, auth_provider, ldap_dn)
                 VALUES (?, ?, ?, 'staff', 'ldap', ?)"
            )->execute([$orgId, $userName, $userEmail, $userDn]);
            $newId = (int)$db->lastInsertId();
            $stmt2 = $db->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt2->execute([$newId]);
            $user = $stmt2->fetch();
        } else {
            // Keep DN current (user may have moved OUs)
            $db->prepare(
                "UPDATE users SET ldap_dn = ?, auth_provider = 'ldap' WHERE user_id = ?"
            )->execute([$userDn, $user['user_id']]);
        }

        return $user ?: null;
    }

    // ── Helpers ──────────────────────────────────────────────────

    /**
     * Return an ordered list of base DNs to search.
     * Uses ldap_contexts (semicolon-separated) if set, falls back to ldap_base_dn.
     */
    private function parseContexts(array $settings): array
    {
        $raw = trim($settings['ldap_contexts'] ?? '');
        if ($raw) {
            $parts = array_filter(array_map('trim', explode(';', $raw)));
            if (!empty($parts)) return array_values($parts);
        }
        $base = trim($settings['ldap_base_dn'] ?? '');
        return $base ? [$base] : [];
    }
}
