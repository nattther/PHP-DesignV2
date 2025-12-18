<?php

declare(strict_types=1);

namespace Design\Path;

/**
 * Builds ProjectPaths without needing to pass __DIR__ from each entry point.
 */
final class ProjectPathsFactory
{
    public function __construct(
        private readonly ProjectRootFinder $rootFinder = new ProjectRootFinder()
    ) {}

    /**
     * @param array<string, mixed> $server Usually $_SERVER
     */
    public function create(array $server = [], string $publicDir = 'public'): ProjectPaths
    {
        $server = $server !== [] ? $server : $_SERVER;

        // Start searching from the current working directory (works for web + CLI)
        $root = $this->rootFinder->find(getcwd());

        return ProjectPaths::fromServer($root, $server, $publicDir);
    }
}
