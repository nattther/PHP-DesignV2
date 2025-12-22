<?php
declare(strict_types=1);

namespace Design\Routing;

enum ViewArea: string
{
    case Public = 'public';
    case Admin  = 'admin';
}

final class ResolvedView
{
    public function __construct(
        private readonly ViewArea $area,
        private readonly string $pageFilePath,
        private readonly string $navbarFilePath,
        private readonly string $footerFilePath,
    ) {}

    public function area(): ViewArea { return $this->area; }
    public function pageFilePath(): string { return $this->pageFilePath; }
    public function navbarFilePath(): string { return $this->navbarFilePath; }
    public function footerFilePath(): string { return $this->footerFilePath; }
}
