<?php
declare(strict_types=1);

namespace Design\Database\Initializer;

use Design\Database\Config\DatabasePaths;
use Design\Logging\LoggerInterface;
use RuntimeException;

final class SqliteDatabaseInitializer implements DatabaseInitializerInterface
{
    public function __construct(
        private readonly DatabasePaths $paths,
        private readonly LoggerInterface $logger,
    ) {}

    public function initialize(): void
    {
        $dbPath    = $this->paths->sqliteDatabasePath();
        $cleanPath = $this->paths->cleanSqliteDatabasePath();

        if ($this->databaseAlreadyExists($dbPath)) {
            return;
        }

        if (!$this->cleanDatabaseExists($cleanPath)) {
            return;
        }

        $this->ensureDirectoryExists(dirname($dbPath), $dbPath);

        $this->copyDatabaseOrFail($cleanPath, $dbPath);

        $this->logInitialized($cleanPath, $dbPath);
    }

    private function databaseAlreadyExists(string $dbPath): bool
    {
        if (!is_file($dbPath)) {
            return false;
        }

        $this->logger->debug('SQLite database already exists', ['path' => $dbPath]);
        return true;
    }

    private function cleanDatabaseExists(string $cleanPath): bool
    {
        if (is_file($cleanPath)) {
            return true;
        }

        $this->logger->warning('Clean SQLite database not found', ['expected' => $cleanPath]);
        return false;
    }

    private function ensureDirectoryExists(string $dir, string $dbPath): void
    {
        if (is_dir($dir)) {
            return;
        }

        $ok = @mkdir($dir, 0775, true);
        if ($ok) {
            return;
        }

        $message = 'Unable to create database directory';
        $this->logger->error($message, [
            'dir' => $dir,
            'dbPath' => $dbPath,
        ]);

        throw new RuntimeException($message . ': ' . $dir);
    }

    private function copyDatabaseOrFail(string $from, string $to): void
    {
        $ok = @copy($from, $to);
        if ($ok) {
            return;
        }

        $message = 'Unable to initialize SQLite database (copy failed)';
        $this->logger->error($message, [
            'from' => $from,
            'to' => $to,
        ]);

        throw new RuntimeException($message);
    }

    private function logInitialized(string $from, string $to): void
    {
        $this->logger->info('SQLite database initialized', [
            'from' => $from,
            'to'   => $to,
        ]);
    }
}
