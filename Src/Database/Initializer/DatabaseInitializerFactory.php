<?php

declare(strict_types=1);

namespace Design\Database\Initializer;

use Design\Database\Config\DatabaseConfig;
use Design\Logging\LoggerInterface;

final class DatabaseInitializerFactory
{
    public static function create(
        DatabaseConfig $config,
        LoggerInterface $logger
    ): DatabaseInitializerInterface {
        if ($config->isSqlite()) {
            return new SqliteDatabaseInitializer($config, $logger);
        }

        return new NoopDatabaseInitializer();
    }
}
