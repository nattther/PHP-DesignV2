<?php
declare(strict_types=1);

namespace Design\Security\Access;

use Design\Auth\AuthContext;
use Design\Http\Exception\ForbiddenHttpException;
use Design\Routing\ResolvedView;
use Design\Routing\ViewArea;

final class AccessChecker
{
    public function __construct(
        private readonly AuthContext $auth
    ) {}

    public function assertCanAccess(ResolvedView $view): void
    {
        if ($view->area() === ViewArea::Admin) {
            $this->assertAdmin();
        }
    }

    private function assertAdmin(): void
    {
        // adapte ici si ton role est un enum
        $roleValue = (string) ($this->auth->role()->value ?? '');
        $isAdmin = ($roleValue === 'admin' || $roleValue === 'ADMIN');

        if (!$isAdmin) {
            throw new ForbiddenHttpException('This page is restricted to administrators.');
        }
    }
}
