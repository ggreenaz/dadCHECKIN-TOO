<?php
namespace App\Core;

use App\Auth\ProviderFactory;
use App\Models\UserModel;

class Auth
{
    /** Local email+password login (used during install before org settings exist) */
    public static function attempt(string $email, string $password, int $orgId): ?array
    {
        $provider = ProviderFactory::make('local');
        $user = $provider->authenticate(
            ['email' => $email, 'password' => $password, 'org_id' => $orgId],
            [],
            ''
        );
        if ($user) self::loginUser($user);
        return $user;
    }

    /** Generic authenticate via any named provider */
    public static function attemptProvider(
        string $providerName,
        array  $credentials,
        array  $orgSettings,
        string $callbackBase = ''
    ): ?array {
        $provider = ProviderFactory::make($providerName);
        if (!$provider) return null;
        $user = $provider->authenticate($credentials, $orgSettings, $callbackBase);
        if ($user) self::loginUser($user);
        return $user;
    }

    /** Load org settings JSON for a given org_id */
    public static function getOrgSettings(int $orgId): array
    {
        $db   = Database::getInstance();
        $stmt = $db->prepare("SELECT settings FROM organizations WHERE organization_id = ?");
        $stmt->execute([$orgId]);
        $row = $stmt->fetch();
        if (!$row || empty($row['settings'])) return ['auth_providers' => ['local']];
        $decoded = json_decode($row['settings'], true);
        return is_array($decoded) ? $decoded : ['auth_providers' => ['local']];
    }

    /** Persist org settings JSON */
    public static function saveOrgSettings(int $orgId, array $settings): void
    {
        $db = Database::getInstance();
        $db->prepare("UPDATE organizations SET settings = ? WHERE organization_id = ?")
           ->execute([json_encode($settings), $orgId]);
    }

    /** Persist user into the session */
    public static function loginUser(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']         = $user['user_id'];
        $_SESSION['user_name']       = $user['name'];
        $_SESSION['user_email']      = $user['email'];
        $_SESSION['user_role']       = $user['role'];
        $_SESSION['organization_id'] = $user['organization_id'];

        $db = Database::getInstance();
        $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?")
           ->execute([$user['user_id']]);
    }

    /** Destroy the session */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool  { return !empty($_SESSION['user_id']); }
    public static function id(): ?int     { return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null; }

    public static function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /** Generate a cryptographically secure state token for OAuth */
    public static function generateState(): string
    {
        return bin2hex(random_bytes(16));
    }
}
