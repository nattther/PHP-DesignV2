<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';


use Design\Error\Handler\HttpErrorHandlerFactory;

use Design\Http\Request;
use Design\Kernel\KernelFactory;
use Design\Routing\LegacyDispatcher;

$kernel = KernelFactory::create($_SERVER);

$request = Request::fromGlobals();

// IMPORTANT: AppPaths, pas ProjectPaths
$appPaths = $kernel->settings()->appPaths();

$dispatcher = new LegacyDispatcher($appPaths);

$errorHandler = (new HttpErrorHandlerFactory())->create(
    paths: $appPaths,
    logger: $kernel->logger(),
);

$errorHandler->handle(fn() => $dispatcher->dispatch($request));
