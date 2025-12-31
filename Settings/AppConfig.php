<?php
declare(strict_types=1);

namespace Design\Settings;

final readonly class AppConfig
{
    public function __construct(
        public string $name,
        public string $faviconIcoRelativePath,
    ) {}
}
