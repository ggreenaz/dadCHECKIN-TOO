<?php
namespace App\Auth;

use App\Auth\Contracts\AuthProvider;
use App\Auth\Providers\LocalProvider;
use App\Auth\Providers\LdapProvider;
use App\Auth\Providers\GoogleProvider;
use App\Auth\Providers\MicrosoftProvider;

class ProviderFactory
{
    /** All registered provider classes */
    private static array $registry = [
        'local'     => LocalProvider::class,
        'ldap'      => LdapProvider::class,
        'google'    => GoogleProvider::class,
        'microsoft' => MicrosoftProvider::class,
    ];

    /** Return all known provider instances */
    public static function all(): array
    {
        return array_map(fn($cls) => new $cls(), self::$registry);
    }

    /** Return provider instances that are both enabled and configured for the given org settings */
    public static function enabled(array $orgSettings): array
    {
        $enabled = $orgSettings['auth_providers'] ?? ['local'];
        $result  = [];
        foreach ($enabled as $name) {
            if (!isset(self::$registry[$name])) continue;
            $provider = new self::$registry[$name]();
            if ($provider->isConfigured($orgSettings)) {
                $result[$name] = $provider;
            }
        }
        // Always fall back to local if nothing else is configured
        if (empty($result)) {
            $result['local'] = new LocalProvider();
        }
        return $result;
    }

    /** Get a single provider by name */
    public static function make(string $name): ?AuthProvider
    {
        if (!isset(self::$registry[$name])) return null;
        return new self::$registry[$name]();
    }
}
