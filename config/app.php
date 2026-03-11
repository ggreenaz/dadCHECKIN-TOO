<?php
// config/app.php

return [
    'name'     => 'dadCHECKIN-TOO',
    'url'      => getenv('APP_URL') ?: 'http://budget2.stgrsd.org',
    'org_slug' => 'southwick-tolland-granville-regional-school-district',   // active organization slug
    'timezone' => getenv('APP_TIMEZONE') ?: 'America/Chicago',
    'debug'    => false,

    'session' => [
        'name'     => 'checkin_sess',
        'lifetime' => 7200,
    ],

    'auth' => [
        'providers' => ['local'],   // 'google', 'ldap' added when configured
    ],

    'google' => [
        'client_id'     => getenv('GOOGLE_CLIENT_ID')     ?: '',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'redirect_uri'  => (getenv('APP_URL') ?: '') . '/auth/google/callback',
    ],

    'ldap' => [
        'host'   => getenv('LDAP_HOST')   ?: '',
        'port'   => (int)(getenv('LDAP_PORT') ?: 389),
        'base_dn'=> getenv('LDAP_BASE_DN')?: '',
    ],
];
