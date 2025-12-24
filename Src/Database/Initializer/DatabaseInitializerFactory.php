<?php
declare(strict_types=1);

namespace Design\Database\Initializer;

use Design\Database\Config\DatabaseConfig;
use Design\Database\Config\DatabasePaths;
use Design\Logging\LoggerInterface;

final class DatabaseInitializerFactory
{
    public static function create(
        DatabaseConfig $config,
        DatabasePaths $paths,
        LoggerInterface $logger
    ): DatabaseInitializerInterface 
    
    {
        if ($config->isSqlite()) {
            return new SqliteDatabaseInitializer($paths, $logger);
        }

        return new NoopDatabaseInitializer();
    }
}
