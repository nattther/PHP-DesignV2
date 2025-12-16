<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Design\Logging\LoggerFactory;
use Design\Settings\SettingsFactory;
use Design\Session\NativeSessionManager;

$logger = (new LoggerFactory(__DIR__))->create();
$settings = SettingsFactory::create();
$sessionConfig = $settings->session();
$session = new NativeSessionManager($sessionConfig, $logger);
$session->start();

