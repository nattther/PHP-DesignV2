<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Design\Http\FrontControllerFactory;
use Design\Http\Request;
use Design\Kernel\KernelFactory;

$kernel = KernelFactory::createForFront($_SERVER);

FrontControllerFactory::create($kernel)
    ->handle(Request::fromGlobals())
    ->send();
