<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Design\Logging\LoggerFactory;

$factory = new LoggerFactory(__DIR__);
$logger = $factory->create();

// Cas simples
$logger->info('App started');
$logger->warning('Slow response', ['ms' => 850]);

// Erreurs sans channel => doit aller dans Errors.log
$logger->error('Database connection failed', ['dsn' => 'mysql:...']);

// Channel explicite avec logger "bound"
$auth = $logger->channel('Auth');
$auth->info('User logged in', ['id' => 42]);
$auth->warning('Too many login attempts', ['id' => 42]);

$db = $logger->channel('Database');
$db->info('Query executed', ['sql' => 'SELECT 1']);

// Channel “sale” (sera nettoyé)
$logger->channel('HTTP/API')->info('Request received', ['path' => '/ping']);

echo "Done.\n";
echo "Check your Logs/ directory:\n";
echo " - Logs/App.log\n";
echo " - Logs/Errors.log\n";
echo " - Logs/Auth.log\n";
echo " - Logs/Database.log\n";
echo " - Logs/HTTPAPI.log (fallback mapping si non défini dans ChannelMap)\n";
