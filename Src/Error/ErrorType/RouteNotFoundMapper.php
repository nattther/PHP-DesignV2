<?php
declare(strict_types=1);

namespace Design\Error\ErrorType;

use Design\Error\ExceptionMapperInterface;
use Design\Error\Object\HttpError;
use Design\Routing\Exception\RouteNotFound;
use Throwable;

final class RouteNotFoundMapper implements ExceptionMapperInterface
{
    public function supports(Throwable $e): bool
    {
        return $e instanceof RouteNotFound;
    }

    public function map(Throwable $e): HttpError
    {
        return new HttpError(404, 'The requested page does not exist.');
    }
}
