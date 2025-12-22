<?php
declare(strict_types=1);

namespace Design\Http\Exception;


final class ForbiddenHttpException extends HttpException
{
    public function __construct(string $message = 'Access forbidden')
    {
        parent::__construct($message, 403);
    }
}
