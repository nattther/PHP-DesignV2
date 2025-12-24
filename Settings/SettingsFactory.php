<?php

declare(strict_types=1);

namespace Design\Settings;

use Design\Path\ProjectPathsFactory;
use Design\Session\Config\SessionConfig;
use Design\Database\Config\DatabaseConfig;
use Design\Auth\Config\AuthConfig;
use Design\Auth\Role\Role;
use Design\Routing\ViewPaths;

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
            cookieSecure: false, // true en prod HTTPS
            cookieHttpOnly: true,
            cookieSameSite: 'Lax',
            useStrictMode: true,
        );

        $driver = 'sqlite';

        $database = new DatabaseConfig(
            driver: $driver,
            projectName: 'design',
            settingsDir: $paths->rootPath . '/Settings',
            databasePath: $paths->rootPath . '/Settings/design.sqlite',
        );

        $adminGroups = ['FR.Hive.Admin'];   // <-- en dur si tu veux
        $publicGroups = ['FR.Hive.Public']; // <-- en dur si tu veux

        $isLocal = self::isLocalhost($server);

        $auth = new AuthConfig(
            localAuthEnabled: $isLocal,      // <-- dev déduit ici
            localForcedRole: Role::Admin,    // <-- forcé en dur
            ssoAdminGroups: $adminGroups,
            ssoPublicGroups: $publicGroups,
        );

                $views = new ViewPaths(
            publicViewsRootPath: $paths->publicPath . DIRECTORY_SEPARATOR . 'public_views',
            adminViewsRootPath:  $paths->publicPath . DIRECTORY_SEPARATOR . 'admin_views',
            errorViewsRootPath:  $paths->publicPath . DIRECTORY_SEPARATOR . 'public_views' . DIRECTORY_SEPARATOR . 'errors',
        );


        return new Settings(
            paths: $paths,
            session: $session,
            database: $database,
            auth: $auth,
            views: $views, 
        );
    }

 private static function isLocalhost(array $server): bool
    {
        $host = (string)($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? '');
        $host = strtolower((string)\preg_replace('/:\d+$/', '', $host));

        if (\in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }

        // Bonus: si tu lances en local via IP (ex: 192.168.x.x) et que tu veux le considérer dev
        $remote = (string)($server['REMOTE_ADDR'] ?? '');
        if (\in_array($remote, ['127.0.0.1', '::1'], true)) {
            return true;
        }

        return false;
    }
}
