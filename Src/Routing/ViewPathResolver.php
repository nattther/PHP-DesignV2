<?php
declare(strict_types=1);

namespace Design\Routing;

use Design\Http\Request;
use Design\Http\Exception\NotFoundHttpException;

final class ViewPathResolver
{
    public function __construct(
        private readonly string $publicViewsRootPath, // ex: <root>/public/public_views
        private readonly string $adminViewsRootPath,  // ex: <root>/public/admin_views
    ) {}

    public function resolve(Request $request): ResolvedView
    {
        $adminPage = $request->queryString('admin_page');
        if ($adminPage !== null) {
            $name = $this->sanitizeName($adminPage);
            return $this->resolveAdmin($name);
        }

        $page = $request->queryString('page') ?? 'home';
        $name = $this->sanitizeName($page);

        // page=... peut être public OU admin : on tente public d’abord, sinon admin
        $public = $this->tryResolvePublic($name);
        if ($public !== null) {
            return $public;
        }

        return $this->resolveAdmin($name);
    }

    private function resolveAdmin(string $name): ResolvedView
    {
        $page = $this->adminViewsRootPath . DIRECTORY_SEPARATOR . $name . '.php';
        if (!is_file($page)) {
            throw new NotFoundHttpException('Admin page not found: ' . $name);
        }

        return new ResolvedView(
            area: ViewArea::Admin,
            pageFilePath: $page,
            navbarFilePath: $this->adminViewsRootPath . DIRECTORY_SEPARATOR . '_navbar.php',
            footerFilePath: $this->adminViewsRootPath . DIRECTORY_SEPARATOR . '_footer.php',
        );
    }

    private function tryResolvePublic(string $name): ?ResolvedView
    {
        $page = $this->publicViewsRootPath . DIRECTORY_SEPARATOR . $name . '.php';
        if (!is_file($page)) {
            return null;
        }

        return new ResolvedView(
            area: ViewArea::Public,
            pageFilePath: $page,
            navbarFilePath: $this->publicViewsRootPath . DIRECTORY_SEPARATOR . '_navbar.php',
            footerFilePath: $this->publicViewsRootPath . DIRECTORY_SEPARATOR . '_footer.php',
        );
    }

    private function sanitizeName(string $name): string
    {
        $name = trim($name);

        // anti ../ + caractères chelous
        if ($name === '' || str_contains($name, '..') || !preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            throw new NotFoundHttpException('Invalid page name');
        }

        return $name;
    }
}
