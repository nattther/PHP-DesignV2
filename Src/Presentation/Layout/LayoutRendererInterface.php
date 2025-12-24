<?php
declare(strict_types=1);

namespace Design\Presentation\Layout;

interface LayoutRendererInterface
{
    /** @param array<string,mixed> $vars */
    public function render(string $viewPath, array $vars = []): void;
}
