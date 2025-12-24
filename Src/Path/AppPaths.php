<?php
declare(strict_types=1);

namespace Design\Path;

final readonly class AppPaths
{
    public function __construct(private ProjectPaths $paths) {}

    public function baseUrl(): string
    {
        return $this->paths->baseUrl;
    }

    public function ajaxDir(): string
    {
        return $this->paths->rootPath . DIRECTORY_SEPARATOR . 'ajax';
    }

    public function controllerDir(): string
    {
        return $this->paths->rootPath . DIRECTORY_SEPARATOR . 'controller';
    }

    public function publicViewsDir(): string
    {
        return $this->paths->publicPath . DIRECTORY_SEPARATOR . 'public_views';
    }

    public function adminViewsDir(): string
    {
        return $this->paths->publicPath . DIRECTORY_SEPARATOR . 'admin_views';
    }

    public function errorViewPath(): string
    {
        return $this->paths->publicPath . DIRECTORY_SEPARATOR . 'public_views' . DIRECTORY_SEPARATOR . 'error.php';
    }
}
