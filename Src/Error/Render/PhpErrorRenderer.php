<?php
declare(strict_types=1);

namespace Design\Error\Render;

use Design\Path\AppPaths;

final readonly class PhpErrorRenderer implements ErrorRendererInterface
{
    public function __construct(private AppPaths $paths) {}

    public function render(int $status, string $message, ?string $requestId = null): void
    {
        http_response_code($status);

        $templatePath = $this->paths->errorViewPath();

        if (!is_file($templatePath)) {
            echo htmlspecialchars((string)$status) . ' - ' . htmlspecialchars($message);
            exit;
        }

        require $templatePath;
        exit;
    }
}
