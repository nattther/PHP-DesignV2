<?php

declare(strict_types=1);

namespace Design\Path;

/**
 * Finds the project root folder on disk.
 *
 * Goal:
 * - Avoid passing __DIR__ everywhere (index.php, health.php, CLI scripts)
 * - Always resolve the same root path (the folder containing composer.json)
 *
 * Strategy:
 * - Start from a given directory (or from this file's directory)
 * - Walk up until we find "composer.json" (and optionally "vendor/")
 */
final class ProjectRootFinder
{
    public function __construct(
        private readonly string $markerFile = 'composer.json'
    ) {}

    /**
     * Tries to find the project root by walking up parent directories.
     *
     * @throws \RuntimeException If the root cannot be found.
     */
    public function find(?string $startDir = null): string
    {
        $dir = $startDir ?? __DIR__;

        $dir = rtrim($dir, DIRECTORY_SEPARATOR);

        while ($dir !== '' && $dir !== dirname($dir)) {
            if ($this->isProjectRoot($dir)) {
                return $dir;
            }

            $dir = dirname($dir);
        }

        throw new \RuntimeException('Unable to locate project root (composer.json not found).');
    }

    private function isProjectRoot(string $dir): bool
    {
        $marker = $dir . DIRECTORY_SEPARATOR . $this->markerFile;

        if (!is_file($marker)) {
            return false;
        }

        // Optional extra safety: vendor/autoload.php exists
        $autoload = $dir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

        return is_file($autoload);
    }
}
