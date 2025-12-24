<?php
declare(strict_types=1);

namespace Design\Routing;

final readonly class ResolvedRoute
{
    private function __construct(
        public string $type,      // view | action | ajax
        public string $path,
        public ?string $area = null, // public | admin (only for view)
    ) {}

    public static function publicView(string $path): self { return new self('view', $path, 'public'); }
    public static function adminView(string $path): self  { return new self('view', $path, 'admin'); }

    public static function action(string $path): self { return new self('action', $path); }
    public static function ajax(string $path): self   { return new self('ajax', $path); }

    public function isView(): bool   { return $this->type === 'view'; }
    public function isAction(): bool { return $this->type === 'action'; }
    public function isAjax(): bool   { return $this->type === 'ajax'; }

    public function isAdminView(): bool  { return $this->isView() && $this->area === 'admin'; }
    public function isPublicView(): bool { return $this->isView() && $this->area === 'public'; }
}
