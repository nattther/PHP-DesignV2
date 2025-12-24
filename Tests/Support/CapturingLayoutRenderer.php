<?php
declare(strict_types=1);

namespace Design\Tests\Support;

final class CapturingLayoutRenderer
{
    public int $calls = 0;
    public ?string $viewPath = null;

    /** @var array<string,mixed> */
    public array $vars = [];

    /** @param array<string,mixed> $vars */
    public function render(string $viewPath, array $vars = []): void
    {
        $this->calls++;
        $this->viewPath = $viewPath;
        $this->vars = $vars;
    }
}
