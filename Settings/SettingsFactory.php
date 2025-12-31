<?php

declare(strict_types=1);

namespace Design\Settings;

use Design\Auth\Config\AuthConfig;
use Design\Auth\Role\Role;
use Design\Database\Config\DatabaseConfig;
use Design\Database\Config\DatabasePaths;
use Design\Environment\EnvironmentDetector;
use Design\Path\AppPaths;
use Design\Path\ProjectPaths;
use Design\Path\ProjectPathsFactory;
use Design\Session\Config\SessionConfig;


final class SettingsFactory
{
    /**
     * @param array<string, mixed> $server
     */
    public static function create(array $server = []): Settings
    {
$paths = self::buildProjectPaths($server);

$appPaths = new AppPaths($paths);

$session  = self::buildSessionConfig();
$auth     = self::buildAuthConfig($server);
$database = self::buildDatabaseConfig();
$databasePaths = self::buildDatabasePaths($paths, $database);
$app = new AppConfig(
    name: 'Lyreco',
    faviconIcoRelativePath: 'assets/img/logo/Lyreco_Logo.ico',
);
return new Settings(
    paths: $paths,
    appPaths: $appPaths,
    session: $session,
    database: $database,
    databasePaths: $databasePaths,
    auth: $auth,
    app: $app,
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


    private static function buildDatabaseConfig(): DatabaseConfig
    {
        return new DatabaseConfig(
            driver: 'sqlite',
            projectName: 'design',
            sqliteFileName: 'design.sqlite',
        );
    }

    private static function buildDatabasePaths(ProjectPaths $paths, DatabaseConfig $database): DatabasePaths
    {
        return new DatabasePaths(
            settingsDirPath: $paths->rootPath . DIRECTORY_SEPARATOR . 'Settings',
            projectName: $database->projectName,
            sqliteFileName: $database->sqliteFileName,
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
}
