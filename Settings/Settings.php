<?php

declare(strict_types=1);

namespace Design\Settings;

use Design\Path\ProjectPaths;
use Design\Session\Config\SessionConfig;
use Design\Database\Config\DatabaseConfig;
use Design\Auth\Config\AuthConfig;
use Design\Database\Config\DatabasePaths;
use Design\Routing\ViewPaths;

final readonly class Settings
{
    public function __construct(
        private ProjectPaths $paths,
        private SessionConfig $session,
        private DatabaseConfig $database,
        private DatabasePaths $databasePaths,
        private AuthConfig $auth,
        private ViewPaths $views,
    ) {}


    public function paths(): ProjectPaths
    {
        return $this->paths;
    }
    public function session(): SessionConfig
    {
        return $this->session;
    }
    public function database(): DatabaseConfig
    {
        return $this->database;
    }
    public function auth(): AuthConfig
    {
        return $this->auth;
    }
    public function views(): ViewPaths
    {
        return $this->views;
    }

    public function databasePaths(): DatabasePaths
    {
        return $this->databasePaths;
    }
}
