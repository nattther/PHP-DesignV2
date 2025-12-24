<?php
declare(strict_types=1);

namespace Design\Presentation\View;

use Design\Auth\AuthContext;
use Design\Routing\ResolvedView;

final class AdminLayoutDecorator
{
    public function __construct(
        private readonly AuthContext $auth,
        private readonly string $adminViewsRootPath,
    ) {}

    public function decorate(ResolvedView $view): ResolvedView
    {
        if (!$this->auth->role()->isAdmin()) {
            return $view;
        }

        // Admin => toujours navbar/footer admin, même si la page est publique
        return new ResolvedView(
            area: $view->area(), // on garde l'area pour ne pas casser la sécurité
            pageFilePath: $view->pageFilePath(),
            navbarFilePath: $this->adminViewsRootPath . DIRECTORY_SEPARATOR . '_navbar.php',
            footerFilePath: $this->adminViewsRootPath . DIRECTORY_SEPARATOR . '_footer.php',
        );
    }
}
