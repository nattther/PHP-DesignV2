<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Design\Http\Request;
use Design\Kernel\KernelFactory;

KernelFactory::create($_SERVER)
    ->frontController()
    ->handle(Request::fromGlobals())
    ->send();
