<?php
namespace App\Auth\Providers;

use App\Auth\Contracts\AuthProvider;
use App\Core\Database;

class LocalProvider implements AuthProvider
{
    public function getName(): string  { return 'local'; }
    public function getLabel(): string { return 'Email & Password'; }

    public function isConfigured(array $settings): bool
    {
        return true; // always available
    }

    public function getAuthUrl(array $settings, string $callbackBase, string $state): ?string
    {
        return null; // form-based
    }

    public function authenticate(array $credentials, array $settings, string $callbackBase): ?array
    {
        $email    = trim($credentials['email'] ?? '');
        $password = $credentials['password'] ?? '';
        $orgId    = (int)($credentials['org_id'] ?? 0);

        if (!$email || !$password || !$orgId) return null;

        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "SELECT * FROM users
             WHERE email = ? AND organization_id = ? AND auth_provider = 'local' AND active = 1"
        );
        $stmt->execute([$email, $orgId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }
}
