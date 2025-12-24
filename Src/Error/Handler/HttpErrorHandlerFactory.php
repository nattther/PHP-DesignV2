<?php
declare(strict_types=1);

namespace Design\Error\Handler;

use Design\Error\ErrorType\BadRouteMapper;
use Design\Error\ErrorType\Default500Mapper;
use Design\Error\ErrorType\RouteNotFoundMapper;
use Design\Error\Render\PhpErrorRenderer;
use Design\Logging\LoggerInterface;
use Design\Path\AppPaths;

final class HttpErrorHandlerFactory
{
    public function create(AppPaths $paths, LoggerInterface $logger): HttpErrorHandler
    {
        return new HttpErrorHandler(
            renderer: new PhpErrorRenderer($paths),
            logger: $logger,
            mappers: [
                new BadRouteMapper(),
                new RouteNotFoundMapper(),
                new Default500Mapper(), 
            ],
        );
    }
}
