<?php

declare(strict_types=1);

namespace Design\Settings;

use Design\Session\SessionConfig;

final class SettingsFactory
{
    public static function create(): Settings
    {
        return new Settings(
            session: new SessionConfig(
                name: 'APPSESSID',
                cookieLifetime: 0,
                cookiePath: '/',
                cookieDomain: '',
                cookieSecure: false,
                cookieHttpOnly: true,
                cookieSameSite: 'Lax',
                useStrictMode: true,
            ),
        );
    }
}
