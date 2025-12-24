<?php
declare(strict_types=1);

namespace Design\Settings;

use Design\Auth\Config\AuthConfig;
use Design\Auth\Role\Role;
use Design\Database\Config\DatabaseConfig;
use Design\Environment\EnvironmentDetector;
use Design\Path\ProjectPaths;
use Design\Path\ProjectPathsFactory;
use Design\Routing\ViewPaths;
use Design\Session\Config\SessionConfig;

final class SettingsFactory
{
    /**
     * @param array<string, mixed> $server
     */
    public static function create(array $server = []): Settings
    {
        $paths = self::buildProjectPaths($server);

        $session  = self::buildSessionConfig();
        $database = self::buildDatabaseConfig($paths);
        $auth     = self::buildAuthConfig($server);
        $views    = self::buildViewPaths($paths);

        return new Settings(
            paths: $paths,
            session: $session,
            database: $database,
            auth: $auth,
            views: $views,
        );
    }

    /**
     * @param array<string, mixed> $server
     */
    private static function buildProjectPaths(array $server): ProjectPaths
    {
        return (new ProjectPathsFactory())->create($server);
    }

    private static function buildSessionConfig(): SessionConfig
    {
        return new SessionConfig(
            name: 'APPSESSID',
            cookieLifetime: 0,
            cookiePath: '/',
            cookieDomain: '',
            cookieSecure: true,
            cookieHttpOnly: true,
            cookieSameSite: 'Lax',
            useStrictMode: true,
        );
    }

    private static function buildDatabaseConfig(ProjectPaths $paths): DatabaseConfig
    {
        $driver = 'sqlite';

        return new DatabaseConfig(
            driver: $driver,
            projectName: 'design',
            settingsDir: $paths->rootPath . '/Settings',
            databasePath: $paths->rootPath . '/Settings/design.sqlite',
        );
    }

    /**
     * @param array<string, mixed> $server
     */
    private static function buildAuthConfig(array $server): AuthConfig
    {
        $adminGroups  = ['FR.Hive.Admin'];
        $publicGroups = ['FR.Hive.Public'];

        $env = new EnvironmentDetector();
        $isLocal = $env->isLocalhost($server);

        return new AuthConfig(
            localAuthEnabled: $isLocal,
            localForcedRole: Role::Admin,
            ssoAdminGroups: $adminGroups,
            ssoPublicGroups: $publicGroups,
        );
    }

    private static function buildViewPaths(ProjectPaths $paths): ViewPaths
    {
        return new ViewPaths(
            publicViewsRootPath: $paths->publicPath . DIRECTORY_SEPARATOR . 'public_views',
            adminViewsRootPath:  $paths->publicPath . DIRECTORY_SEPARATOR . 'admin_views',
            errorViewsRootPath:  $paths->publicPath . DIRECTORY_SEPARATOR . 'public_views' . DIRECTORY_SEPARATOR . 'errors',
        );
    }


}
