<?php

declare(strict_types=1);

namespace Design\Path;

final readonly class AssetPaths
{
    public function __construct(
        private ProjectPaths $projectPaths,
        private AppPaths $appPaths,
    ) {}

    public function cssFilePath(string $file): string
    {
        return rtrim($this->projectPaths->assetsPath, '/\\')
            . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . ltrim($file, '/');
    }

    public function cssUrl(string $file): string
    {
        return rtrim($this->appPaths->baseUrl(), '/') . '/assets/css/' . ltrim($file, '/');
    }

    public function jsFilePath(string $relative): string
    {
        return rtrim($this->projectPaths->assetsPath, '/\\')
            . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . ltrim($relative, '/');
    }

    public function jsUrl(string $relative): string
    {
        return rtrim($this->appPaths->baseUrl(), '/') . '/assets/js/' . ltrim($relative, '/');
    }

    public function assetUrl(string $relative): string
    {
        return rtrim($this->appPaths->baseUrl(), '/') . '/' . ltrim($relative, '/');
    }
}
