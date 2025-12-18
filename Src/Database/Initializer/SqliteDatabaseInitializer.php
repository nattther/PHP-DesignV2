<?php

declare(strict_types=1);

namespace Design\Database\Initializer;

use Design\Database\Config\DatabaseConfig;
use Design\Logging\LoggerInterface;

final class SqliteDatabaseInitializer implements DatabaseInitializerInterface
{
    public function __construct(
        private readonly DatabaseConfig $config,
        private readonly LoggerInterface $logger,
    ) {}

    public function initialize(): void
    {
        if (!$this->config->isSqlite()) {
            $this->logger->debug('Database init skipped (not sqlite)');
            return;
        }

        if (is_file($this->config->databasePath)) {
            $this->logger->debug('SQLite database already exists', [
                'path' => $this->config->databasePath,
            ]);
            return;
        }

        if (!is_file($this->config->cleanDatabasePath())) {
            $this->logger->warning('Clean SQLite database not found', [
                'expected' => $this->config->cleanDatabasePath(),
            ]);
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

        $this->logger->info('SQLite database initialized', [
            'from' => $this->config->cleanDatabasePath(),
            'to'   => $this->config->databasePath,
        ]);
    }
}
