<?php
declare(strict_types=1);

namespace Design\Presentation\Layout;

use Design\Auth\AuthContext;
use Design\Path\AppPaths;
use RuntimeException;

final readonly class LayoutRenderer
{
    public function __construct(
        private AppPaths $paths,
        private AuthContext $auth,
    ) {}

    /**
     * @param array<string, mixed> $vars Variables available inside the view.
     */
    public function render(string $viewPath, array $vars = []): void
    {
        [$navbar, $footer] = $this->selectLayoutParts();

        $this->assertFile($navbar);
        $this->assertFile($viewPath);
        $this->assertFile($footer);

          $vars += [
        'auth' => $this->auth,
        'baseUrl' => $this->paths->baseUrl(),
    ];

        // Expose variables to the view scope
        extract($vars, EXTR_SKIP);

        require $navbar;
        require $viewPath;
        require $footer;
    }

    /**
     * @return array{0:string,1:string}
     */
    private function selectLayoutParts(): array
    {
        if ($this->auth->isAdmin()) {
            return [$this->paths->adminNavbarPath(), $this->paths->adminFooterPath()];
        }

        return [$this->paths->publicNavbarPath(), $this->paths->publicFooterPath()];
    }

    private function assertFile(string $path): void
    {
        if (!is_file($path)) {
            throw new RuntimeException("Layout file missing: {$path}");
        }
    }
}
