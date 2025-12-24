<?php
declare(strict_types=1);

namespace Design\Routing;

final readonly class ViewPaths
{
    public function __construct(
        private string $publicViewsRootPath,
        private string $adminViewsRootPath,
        private string $errorViewsRootPath,
    ) {}

    public function publicViewsRootPath(): string { return $this->publicViewsRootPath; }
    public function adminViewsRootPath(): string { return $this->adminViewsRootPath; }
    public function errorViewsRootPath(): string { return $this->errorViewsRootPath; }

    public function publicNavbarPath(): string
    {
        return $this->publicViewsRootPath . DIRECTORY_SEPARATOR . '_navbar.php';
    }

    public function publicFooterPath(): string
    {
        return $this->publicViewsRootPath . DIRECTORY_SEPARATOR . '_footer.php';
    }

    public function adminNavbarPath(): string
    {
        return $this->adminViewsRootPath . DIRECTORY_SEPARATOR . '_navbar.php';
    }

    public function adminFooterPath(): string
    {
        return $this->adminViewsRootPath . DIRECTORY_SEPARATOR . '_footer.php';
    }

    public function errorViewPath(string $name): string
    {
        return $this->errorViewsRootPath . DIRECTORY_SEPARATOR . $name . '.php';
    }
}
