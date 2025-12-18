<?php

declare(strict_types=1);

namespace Design\Tests\Support;

use Design\Logging\Writer\FileWriterInterface;

/**
 * Writer that stores logs in memory instead of filesystem.
 */
final class InMemoryWriter implements FileWriterInterface
{
    /** @var array<int, array{path: string, content: string}> */
    public array $writes = [];

    public function append(string $filePath, string $content): void
    {
        $this->writes[] = [
            'path' => $filePath,
            'content' => $content,
        ];
    }
}
