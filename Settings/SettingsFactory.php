<?php

declare(strict_types=1);

namespace Design\Settings;

use Design\Path\ProjectPathsFactory;
use Design\Session\Config\SessionConfig;

final class SettingsFactory
{
    public static function create(array $server = []): Settings
    {
        $paths = (new ProjectPathsFactory())->create($server);

        $session = new SessionConfig(
            name: 'APPSESSID',
            cookieLifetime: 0,
            cookiePath: '/',
            cookieDomain: '',
            cookieSecure: false,
            cookieHttpOnly: true,
            cookieSameSite: 'Lax',
            useStrictMode: true,
        );

        return new Settings(
            paths: $paths,
            session: $session,
        );
    }
}
