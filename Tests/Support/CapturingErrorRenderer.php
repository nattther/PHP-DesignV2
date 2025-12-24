<?php
declare(strict_types=1);

namespace Design\Tests\Support;

use Design\Error\Render\ErrorRendererInterface;

final class CapturingErrorRenderer implements ErrorRendererInterface
{
    public ?int $status = null;
    public ?string $message = null;
    public ?string $requestId = null;

    public function render(int $status, string $message, ?string $requestId = null): void
    {
        $this->status = $status;
        $this->message = $message;
        $this->requestId = $requestId;
    }
}
