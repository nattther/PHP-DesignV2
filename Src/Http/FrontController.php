<?php
declare(strict_types=1);

namespace Design\Http;

use Design\Kernel\Kernel;
use Design\Path\AssetPaths;
use Design\Presentation\Layout\LayoutRenderer;
use Design\Routing\LegacyDispatcher;
use Design\Security\Access\RouteAccessGuard;
use Design\Security\Access\RouteMethodGuard;
use Design\Security\Csrf\CsrfGuard;

final readonly class FrontController
{
    public function __construct(
        private Kernel $kernel,
        private LegacyDispatcher $dispatcher,
        private LayoutRenderer $layout,
        private RouteAccessGuard $accessGuard,
        private RouteMethodGuard $methodGuard,
        private CsrfGuard $csrfGuard,
    ) {}

    public function handle(Request $request): void
    {
        $route = $this->dispatcher->resolve($request);

        $this->accessGuard->assertAllowed($route);
        $this->methodGuard->assertAllowedMethod($request, $route);
        $this->csrfGuard->assertValidForPost($request);

        if ($route->isAjax() || $route->isAction()) {
            require $route->path;
            return;
        }

        $viewName = pathinfo($route->path, PATHINFO_FILENAME); // home, about, ...


        $this->layout->render($route->path, [
            'kernel'    => $this->kernel, // garde-le si tes views en ont besoin
            'viewName'  => $viewName,
            'routeArea' => $route->area ?? 'public',
        ]);
    }
}
