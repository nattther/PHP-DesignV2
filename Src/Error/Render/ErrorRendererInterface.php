<?php
declare(strict_types=1);

namespace Design\Error\Render;

interface ErrorRendererInterface
{
    public function render(int $status, string $message, ?string $requestId = null): void;
}
