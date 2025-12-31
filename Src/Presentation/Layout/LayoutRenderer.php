<?php
declare(strict_types=1);

namespace Design\Presentation\Layout;

use Design\Auth\AuthContext;
use Design\Path\AppPaths;
use Design\Path\AssetPaths;
use Design\Settings\AppConfig;
use RuntimeException;

final readonly class LayoutRenderer
{
    public function __construct(
        private AppPaths $paths,
        private AssetPaths $assets,
        private AuthContext $auth,
        private AppConfig $app,
    ) {}

    /**
     * @param array<string, mixed> $vars
     */
    public function render(string $viewPath, array $vars = []): void
    {
        [$navbar, $footer] = $this->selectLayoutParts();

        $this->assertFile($navbar);
        $this->assertFile($viewPath);
        $this->assertFile($footer);

        // ✅ Variables globales dispo partout (navbar/view/footer)
        $vars += [
            'auth' => $this->auth,
            'baseUrl' => $this->paths->baseUrl(),
            'assets' => $this->assets,

            'appName' => $this->app->name,
            'faviconRelative' => $this->app->faviconIcoRelativePath,
        ];

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
