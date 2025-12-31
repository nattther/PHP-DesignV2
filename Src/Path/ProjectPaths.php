<?php

declare(strict_types=1);

namespace Design\Path;

/**
 * Centralises filesystem paths and base URL for the project.
 *
 * Goal:
 * - Always know the absolute project root on disk (ex: C:\...\PHP-DesignV2)
 * - Always know the base URL used by the web server (ex: /PHP-DesignV2 or "")
 *
 * This avoids guessing in each script (index.php, health.php, CLI scripts, etc.).
 */
final readonly class ProjectPaths
{
    public function __construct(
        public string $rootPath,
        public string $publicPath,
        public string $logsPath,
        public string $baseUrl,
        public string $assetsPath,
    ) {}

    /**
     * Builds the paths from a known project root (usually __DIR__ of a bootstrap file)
     * and the web server environment (usually $_SERVER).
     *
     * If the project root is inside DOCUMENT_ROOT, baseUrl is computed from it.
     * Otherwise, baseUrl is "" (works for CLI or unusual server setups).
     *
     * @param array<string, mixed> $server
     */
    public static function fromServer(string $projectRootPath, array $server, string $publicDir = 'public'): self
    {
        $root = rtrim(self::normalizePath($projectRootPath), '/');
        $docRoot = isset($server['DOCUMENT_ROOT']) ? rtrim(self::normalizePath((string) $server['DOCUMENT_ROOT']), '/') : '';

        $baseUrl = '';
        if ($docRoot !== '' && str_starts_with($root . '/', $docRoot . '/')) {
            $relative = substr($root, strlen($docRoot));
            $relative = '/' . ltrim($relative, '/');
            $baseUrl = $relative === '/' ? '' : $relative;
        }
        return new self(
            rootPath: $root,
            publicPath: $root . '/' . trim($publicDir, '/'),
            logsPath: $root . '/Logs',
            baseUrl: $baseUrl,
            assetsPath: $root . '/assets', // ✅ assets at root
        );

    }

    

    private static function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
