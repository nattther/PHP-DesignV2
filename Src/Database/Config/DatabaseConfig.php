<?php

declare(strict_types=1);

namespace Design\Database\Config;

final readonly class DatabaseConfig
{
    public function __construct(
        public string $driver,          // sqlite | mysql
        public string $databasePath,    // chemin final du .sqlite
        public string $projectName,     // monprojet
        public string $settingsDir,     // chemin vers /Settings
    ) {}

    public function isSqlite(): bool
    {
        return $this->driver === 'sqlite';
    }

        public function shouldInitializeSqlite(): bool
    {
        return $this->isSqlite()
            && !is_file($this->databasePath)
            && is_file($this->cleanDatabasePath());
    }

    public function cleanDatabasePath(): string
    {
        return $this->settingsDir
            . DIRECTORY_SEPARATOR
            . 'clean_' . $this->projectName . '.sqlite';
    }

    public function databaseDirectory(): string
    {
        return dirname($this->databasePath);
    }
}
