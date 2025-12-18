<?php

declare(strict_types=1);

namespace Design\Database\Initializer;

use Design\Database\Config\DatabaseConfig;

final class DatabaseInitializerFactory
{
    public static function create(DatabaseConfig $config): DatabaseInitializerInterface
    {
        if ($config->isSqlite()) {
            return new SqliteDatabaseInitializer($config);
        }

        return new NoopDatabaseInitializer();
    }
}
