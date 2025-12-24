<?php
declare(strict_types=1);

namespace Design\Settings;

use Design\Path\ProjectPaths;
use Design\Path\AppPaths;
use Design\Session\Config\SessionConfig;
use Design\Database\Config\DatabaseConfig;
use Design\Database\Config\DatabasePaths;
use Design\Auth\Config\AuthConfig;

final readonly class Settings
{
    public function __construct(
        private ProjectPaths $paths,
        private AppPaths $appPaths,
        private SessionConfig $session,
        private DatabaseConfig $database,
        private DatabasePaths $databasePaths,
        private AuthConfig $auth,
    ) {}

    public function paths(): ProjectPaths { return $this->paths; }
    public function appPaths(): AppPaths { return $this->appPaths; }

    public function session(): SessionConfig { return $this->session; }
    public function database(): DatabaseConfig { return $this->database; }
    public function databasePaths(): DatabasePaths { return $this->databasePaths; }
    public function auth(): AuthConfig { return $this->auth; }


}
