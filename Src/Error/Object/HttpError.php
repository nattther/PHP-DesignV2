<?php
declare(strict_types=1);

namespace Design\Error\Object;

final readonly class HttpError
{
    public function __construct(
        public int $status,
        public string $publicMessage,
        public ?string $requestId = null,
    ) {}
}
