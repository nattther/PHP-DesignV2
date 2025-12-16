<?php

declare(strict_types=1);

namespace Design\Tests\LoggingTest;

use Design\Logging\LoggerFactory;
use PHPUnit\Framework\TestCase;

final class LoggingIntegrationTest extends TestCase
{
    private string $tmpProjectRoot;

    protected function setUp(): void
    {
        $this->tmpProjectRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php_design_logging_' . bin2hex(random_bytes(4));
        mkdir($this->tmpProjectRoot, 0775, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->tmpProjectRoot);
    }

    public function test_full_pipeline_writes_expected_files_and_lines(): void
    {
        $logger = (new LoggerFactory($this->tmpProjectRoot))->create();

        // App (default)
        $logger->info('App started');

        // Errors (policy: error/critical without channel => Errors)
        $logger->error('Database down', ['dsn' => 'mysql:...']);

        // Bound channel: Auth
        $logger->channel('Auth')->info('User logged in', ['id' => 42]);

        // “Dirty” channel name => sanitized => HTTPAPI
        $logger->channel('HTTP/API')->warning('Ping', ['path' => '/ping']);

        $logsDir = $this->tmpProjectRoot . DIRECTORY_SEPARATOR . 'Logs';
        self::assertDirectoryExists($logsDir);

        // Check files exist
        self::assertFileExists($logsDir . DIRECTORY_SEPARATOR . 'App.log');
        self::assertFileExists($logsDir . DIRECTORY_SEPARATOR . 'Errors.log');
        self::assertFileExists($logsDir . DIRECTORY_SEPARATOR . 'Auth.log');
        self::assertFileExists($logsDir . DIRECTORY_SEPARATOR . 'HTTPAPI.log');

        // Check content (very lightweight assertions)
        $app = file_get_contents($logsDir . DIRECTORY_SEPARATOR . 'App.log');
        self::assertIsString($app);
        self::assertStringContainsString('INFO', $app);
        self::assertStringContainsString('App started', $app);

        $errors = file_get_contents($logsDir . DIRECTORY_SEPARATOR . 'Errors.log');
        self::assertIsString($errors);
        self::assertStringContainsString('ERROR', $errors);
        self::assertStringContainsString('Database down', $errors);
        self::assertStringContainsString('"dsn":"mysql:..."', $errors);

        $auth = file_get_contents($logsDir . DIRECTORY_SEPARATOR . 'Auth.log');
        self::assertIsString($auth);
        self::assertStringContainsString('INFO', $auth);
        self::assertStringContainsString('User logged in', $auth);
        self::assertStringContainsString('"id":42', $auth);

        $http = file_get_contents($logsDir . DIRECTORY_SEPARATOR . 'HTTPAPI.log');
        self::assertIsString($http);
        self::assertStringContainsString('WARNING', $http);
        self::assertStringContainsString('Ping', $http);
        self::assertStringContainsString('"path":"\/ping"', $http);
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
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
