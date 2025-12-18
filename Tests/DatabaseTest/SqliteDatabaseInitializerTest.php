<?php

declare(strict_types=1);

namespace Design\Tests\DatabaseTest;

use Design\Database\Config\DatabaseConfig;
use Design\Database\Initializer\SqliteDatabaseInitializer;
use Design\Database\Initializer\NoopDatabaseInitializer;
use PHPUnit\Framework\TestCase;

final class SqliteDatabaseInitializerTest extends TestCase
{
    public function test_sqlite_initializer_copies_clean_database_when_missing(): void
    {
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'db_init_' . bin2hex(random_bytes(4));
        mkdir($tmp, 0775, true);

        $settingsDir = $tmp . '/Settings';
        $dbDir = $tmp . '/var';

        mkdir($settingsDir, 0775, true);

        $cleanDb = $settingsDir . '/clean_test.sqlite';
        $targetDb = $dbDir . '/test.sqlite';

        file_put_contents($cleanDb, 'CLEAN_DB');

        $config = new DatabaseConfig(
            driver: 'sqlite',
            databasePath: $targetDb,
            projectName: 'test',
            settingsDir: $settingsDir,
        );

        $initializer = new SqliteDatabaseInitializer($config);
        $initializer->initialize();

        self::assertFileExists($targetDb);
        self::assertSame('CLEAN_DB', file_get_contents($targetDb));

        $this->deleteDir($tmp);
    }

    public function test_sqlite_initializer_is_idempotent(): void
    {
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'db_init_' . bin2hex(random_bytes(4));
        mkdir($tmp, 0775, true);

        $settingsDir = $tmp . '/Settings';
        $dbDir = $tmp . '/var';

        mkdir($settingsDir, 0775, true);
        mkdir($dbDir, 0775, true);

        $cleanDb = $settingsDir . '/clean_test.sqlite';
        $targetDb = $dbDir . '/test.sqlite';

        file_put_contents($cleanDb, 'CLEAN_DB');
        file_put_contents($targetDb, 'EXISTING_DB');

        $config = new DatabaseConfig(
            driver: 'sqlite',
            databasePath: $targetDb,
            projectName: 'test',
            settingsDir: $settingsDir,
        );

        $initializer = new SqliteDatabaseInitializer($config);
        $initializer->initialize();

        // Must NOT overwrite existing database
        self::assertSame('EXISTING_DB', file_get_contents($targetDb));

        $this->deleteDir($tmp);
    }

    public function test_noop_initializer_does_nothing(): void
    {
        $initializer = new NoopDatabaseInitializer();

        // Should never throw
        $initializer->initialize();

        self::assertTrue(true);
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($dir);
    }
}
