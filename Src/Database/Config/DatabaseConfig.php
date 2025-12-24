<?php
declare(strict_types=1);

namespace Design\Database\Config;

final readonly class DatabaseConfig
{
    public function __construct(
        public string $driver,          // sqlite | mysql
        public string $projectName,     // design
        public string $sqliteFileName,  // design.sqlite
        public string $settingsDirName = 'Settings', // dossier settings
    ) {}

    public function isSqlite(): bool
    {
        return $this->driver === 'sqlite';
    }
}
