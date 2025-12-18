<?php

declare(strict_types=1);

namespace Design\Database\Initializer;

use Design\Database\Config\DatabaseConfig;

final class SqliteDatabaseInitializer implements DatabaseInitializerInterface
{
    public function __construct(
        private readonly DatabaseConfig $config,
    ) {}

    public function initialize(): void
    {
        if (!$this->config->shouldInitializeSqlite()) {
            return;
        }

        $dir = $this->config->databaseDirectory();
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        copy(
            $this->config->cleanDatabasePath(),
            $this->config->databasePath
        );
    }
}
