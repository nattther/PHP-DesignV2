<?php

declare(strict_types=1);

namespace Design\Settings;

use Design\Path\ProjectPaths;
use Design\Session\Config\SessionConfig;

final readonly class Settings
{
    public function __construct(
        private ProjectPaths $paths,
        private SessionConfig $session,
    ) {}

    public function paths(): ProjectPaths
    {
        return $this->paths;
    }

    public function session(): SessionConfig
    {
        return $this->session;
    }
}
