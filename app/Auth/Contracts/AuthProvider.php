<?php
namespace App\Auth\Contracts;

interface AuthProvider
{
    /** Machine name: 'local', 'ldap', 'google', 'microsoft' */
    public function getName(): string;

    /** Human-readable label shown on the login page */
    public function getLabel(): string;

    /** Whether this provider has enough config to function */
    public function isConfigured(array $settings): bool;

    /**
     * For OAuth providers: return the authorization redirect URL.
     * For form-based providers (local, ldap): return null.
     */
    public function getAuthUrl(array $settings, string $callbackBase, string $state): ?string;

    /**
     * Authenticate and return a partial user array on success, or null on failure.
     * $credentials keys depend on the provider:
     *   local/ldap  → ['email'|'username', 'password']
     *   google/microsoft → ['code', 'state', 'session_state']
     */
    public function authenticate(array $credentials, array $settings, string $callbackBase): ?array;
}
