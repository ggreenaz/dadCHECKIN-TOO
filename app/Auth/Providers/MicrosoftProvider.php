<?php
namespace App\Auth\Providers;

use App\Auth\Contracts\AuthProvider;
use App\Core\Database;

class MicrosoftProvider implements AuthProvider
{
    public function getName(): string  { return 'microsoft'; }
    public function getLabel(): string { return 'Sign in with Microsoft'; }

    public function isConfigured(array $settings): bool
    {
        return !empty($settings['microsoft_client_id']) && !empty($settings['microsoft_client_secret']);
    }

    public function getAuthUrl(array $settings, string $callbackBase, string $state): ?string
    {
        $tenant = $settings['microsoft_tenant_id'] ?? 'common';
        return "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/authorize?" . http_build_query([
            'client_id'     => $settings['microsoft_client_id'],
            'redirect_uri'  => $callbackBase . '/auth/callback/microsoft',
            'response_type' => 'code',
            'scope'         => 'openid email profile User.Read',
            'state'         => $state,
            'response_mode' => 'query',
        ]);
    }

    public function authenticate(array $credentials, array $settings, string $callbackBase): ?array
    {
        $code  = $credentials['code']  ?? '';
        $orgId = (int)($credentials['org_id'] ?? 0);
        if (!$code || !$orgId) return null;

        $tenant   = $settings['microsoft_tenant_id'] ?? 'common';
        $tokenUrl = "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";

        $token = $this->post($tokenUrl, [
            'code'          => $code,
            'client_id'     => $settings['microsoft_client_id'],
            'client_secret' => $settings['microsoft_client_secret'],
            'redirect_uri'  => $callbackBase . '/auth/callback/microsoft',
            'grant_type'    => 'authorization_code',
            'scope'         => 'openid email profile User.Read',
        ]);

        if (empty($token['access_token'])) return null;

        // Fetch user info from Microsoft Graph
        $info = $this->get('https://graph.microsoft.com/v1.0/me', $token['access_token']);
        if (empty($info['mail']) && empty($info['userPrincipalName'])) return null;

        $microsoftId = $info['id']                 ?? '';
        $email       = $info['mail']               ?? $info['userPrincipalName'] ?? '';
        $name        = $info['displayName']        ?? $email;

        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT * FROM users
             WHERE organization_id = ? AND (microsoft_id = ? OR email = ?) AND active = 1"
        );
        $stmt->execute([$orgId, $microsoftId, $email]);
        $user = $stmt->fetch();

        if (!$user) return null; // must be pre-provisioned by admin

        $db->prepare("UPDATE users SET microsoft_id = ?, auth_provider = 'microsoft' WHERE user_id = ?")
           ->execute([$microsoftId, $user['user_id']]);

        return $user;
    }

    private function post(string $url, array $data): array
    {
        $ctx = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data),
            'timeout' => 10,
        ]]);
        $body = @file_get_contents($url, false, $ctx);
        return $body ? (json_decode($body, true) ?? []) : [];
    }

    private function get(string $url, string $accessToken): array
    {
        $ctx = stream_context_create(['http' => [
            'method'  => 'GET',
            'header'  => "Authorization: Bearer {$accessToken}\r\n",
            'timeout' => 10,
        ]]);
        $body = @file_get_contents($url, false, $ctx);
        return $body ? (json_decode($body, true) ?? []) : [];
    }
}
