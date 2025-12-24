<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Design\Error\Handler\HttpErrorHandlerFactory;
use Design\Http\FrontControllerFactory;
use Design\Http\Request;
use Design\Kernel\KernelFactory;

$kernel = KernelFactory::create($_SERVER);

$request = Request::fromGlobals();

$errorHandler = (new HttpErrorHandlerFactory())->create(
    paths: $kernel->settings()->appPaths(),
    logger: $kernel->logger(),
);

$front = (new FrontControllerFactory())->create($kernel);

$errorHandler->handle(fn() => $front->handle($request));
