<?php

declare(strict_types=1);

namespace Design\Tests\DatabaseTest;

use Design\Database\Config\DatabaseConfig;
use Design\Database\Initializer\SqliteDatabaseInitializer;
use Design\Database\Initializer\NoopDatabaseInitializer;
use Design\Logging\FileLogger;
use Design\Logging\LineFormatter\SimpleLogLineFormatter;
use Design\Logging\Path\FilePathResolver;
use Design\Logging\Path\LogsDirectory;
use Design\Logging\Policy\DefaultChannelPolicy;
use Design\Logging\Context\JsonContextEncoder;
use Design\Logging\ValueObject\ChannelMap;
use Design\Tests\Support\FakeClock;
use Design\Tests\Support\InMemoryWriter;
use PHPUnit\Framework\TestCase;

final class SqliteDatabaseInitializerTest extends TestCase
{
    public function test_sqlite_initializer_copies_clean_database_when_missing(): void
    {
        [$tmp, $logger, $writer] = $this->buildLogger();

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

        $initializer = new SqliteDatabaseInitializer($config, $logger);
        $initializer->initialize();

        self::assertFileExists($targetDb);
        self::assertSame('CLEAN_DB', file_get_contents($targetDb));

        $logs = implode("\n", array_column($writer->writes, 'content'));
        self::assertStringContainsString('SQLite database initialized', $logs);

        $this->deleteDir($tmp);
    }

    public function test_sqlite_initializer_is_idempotent(): void
    {
        [$tmp, $logger, $writer] = $this->buildLogger();

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

        $initializer = new SqliteDatabaseInitializer($config, $logger);
        $initializer->initialize();

        self::assertSame('EXISTING_DB', file_get_contents($targetDb));

        $logs = implode("\n", array_column($writer->writes, 'content'));
        self::assertStringContainsString('SQLite database already exists', $logs);

        $this->deleteDir($tmp);
    }

    public function test_noop_initializer_does_nothing(): void
    {
        $initializer = new NoopDatabaseInitializer();

        $initializer->initialize();

        self::assertTrue(true);
    }

    /**
     * @return array{0:string,1:\Design\Logging\LoggerInterface,2:InMemoryWriter}
     */
    private function buildLogger(): array
    {
        $tmp = sys_get_temp_dir() . '/db_init_' . bin2hex(random_bytes(4));
        mkdir($tmp, 0775, true);

        $logsDir = LogsDirectory::fromPath($tmp . '/Logs');
        $resolver = new FilePathResolver($logsDir, ChannelMap::defaults());

        $writer = new InMemoryWriter();

        $logger = new FileLogger(
            clock: new FakeClock('2025-12-16T10:00:00+01:00'),
            pathResolver: $resolver,
            formatter: new SimpleLogLineFormatter(new JsonContextEncoder()),
            writer: $writer,
            channelPolicy: DefaultChannelPolicy::standard(),
        );

        return [$tmp, $logger->channel('Database'), $writer];
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
