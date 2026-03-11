<?php
namespace App\Auth\Providers;

use App\Auth\Contracts\AuthProvider;
use App\Core\Database;

class GoogleProvider implements AuthProvider
{
    private const AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const INFO_URL  = 'https://www.googleapis.com/oauth2/v3/userinfo';

    public function getName(): string  { return 'google'; }
    public function getLabel(): string { return 'Sign in with Google'; }

    public function isConfigured(array $settings): bool
    {
        return !empty($settings['google_client_id']) && !empty($settings['google_client_secret']);
    }

    public function getAuthUrl(array $settings, string $callbackBase, string $state): ?string
    {
        return self::AUTH_URL . '?' . http_build_query([
            'client_id'     => $settings['google_client_id'],
            'redirect_uri'  => $callbackBase . '/auth/callback/google',
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
        ]);
    }

    public function authenticate(array $credentials, array $settings, string $callbackBase): ?array
    {
        $code  = $credentials['code']  ?? '';
        $orgId = (int)($credentials['org_id'] ?? 0);
        if (!$code || !$orgId) return null;

        // Exchange code for token
        $token = $this->post(self::TOKEN_URL, [
            'code'          => $code,
            'client_id'     => $settings['google_client_id'],
            'client_secret' => $settings['google_client_secret'],
            'redirect_uri'  => $callbackBase . '/auth/callback/google',
            'grant_type'    => 'authorization_code',
        ]);

        if (empty($token['access_token'])) return null;

        // Fetch user info
        $info = $this->get(self::INFO_URL, $token['access_token']);
        if (empty($info['email'])) return null;

        $googleId = $info['sub']   ?? '';
        $email    = $info['email'] ?? '';
        $name     = $info['name']  ?? $email;

        // Find existing user
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT * FROM users
             WHERE organization_id = ? AND (google_id = ? OR email = ?) AND active = 1"
        );
        $stmt->execute([$orgId, $googleId, $email]);
        $user = $stmt->fetch();

        if (!$user) return null; // must be pre-provisioned by admin

        // Keep google_id in sync
        $db->prepare("UPDATE users SET google_id = ?, auth_provider = 'google' WHERE user_id = ?")
           ->execute([$googleId, $user['user_id']]);

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
