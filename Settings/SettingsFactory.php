<?php

declare(strict_types=1);

namespace Design\Settings;

use Design\Path\ProjectPathsFactory;
use Design\Session\Config\SessionConfig;
use Design\Database\Config\DatabaseConfig;

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

        // ---- DATABASE ----
        $driver = $_ENV['DB_DRIVER'] ?? 'sqlite';

        $database = new DatabaseConfig(
            driver: $driver,
            projectName: 'design',
            settingsDir: $paths->rootPath . '/Settings',
            databasePath: $paths->rootPath . '/Settings/design.sqlite',
        );

        return new Settings(
            paths: $paths,
            session: $session,
            database: $database,
        );
    }
}
