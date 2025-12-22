<?php
declare(strict_types=1);

namespace Design\Presentation\View;

use Design\Http\Exception\NotFoundHttpException;
use Design\Routing\ResolvedView;

final class CompositePhpRenderer
{
    /**
     * @param array<string, mixed> $data
     */
    public function render(ResolvedView $view, array $data = []): string
    {
        $this->assertFile($view->navbarFilePath());
        $this->assertFile($view->pageFilePath());
        $this->assertFile($view->footerFilePath());

        extract($data, EXTR_SKIP);

        ob_start();
        require $view->navbarFilePath();
        require $view->pageFilePath();
        require $view->footerFilePath();
        return (string) ob_get_clean();
    }

    private function assertFile(string $path): void
    {
        if (!is_file($path)) {
            throw new NotFoundHttpException('View file missing: ' . $path);
        }
    }
}
