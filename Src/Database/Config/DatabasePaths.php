<?php
declare(strict_types=1);

namespace Design\Database\Config;

final readonly class DatabasePaths
{
    public function __construct(
        private string $settingsDirPath,  // ex: <root>/Settings
        private string $projectName,      // ex: design
        private string $sqliteFileName,   // ex: design.sqlite
    ) {}

    public function settingsDirPath(): string
    {
        return $this->settingsDirPath;
    }

    public function sqliteDatabasePath(): string
    {
        return $this->settingsDirPath . DIRECTORY_SEPARATOR . $this->sqliteFileName;
    }

    public function cleanSqliteDatabasePath(): string
    {
        return $this->settingsDirPath
            . DIRECTORY_SEPARATOR
            . 'clean_' . $this->projectName . '.sqlite';
    }
}
