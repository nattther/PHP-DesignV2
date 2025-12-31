<?php
declare(strict_types=1);

namespace Design\Http;

use Design\Kernel\Kernel;

use Design\Routing\LegacyDispatcher;
use Design\Security\Access\RouteAccessGuard;
use Design\Security\Access\RouteMethodGuard;
use Design\Security\Csrf\CsrfGuard;

use Design\Path\AssetPaths;
use Design\Presentation\Layout\LayoutRenderer;

final class FrontControllerFactory
{
    public function create(\Design\Kernel\Kernel $kernel): FrontController
    {
        $appPaths = $kernel->settings()->appPaths();

        $assets = new AssetPaths(
            projectPaths: $kernel->settings()->paths(),
            appPaths: $appPaths,
        );

        return new FrontController(
            kernel: $kernel,
            dispatcher: new LegacyDispatcher($appPaths),
            layout: new LayoutRenderer(
                paths: $appPaths,
                assets: $assets,
                auth: $kernel->auth(),
                app: $kernel->settings()->app(),
            ),
            accessGuard: new RouteAccessGuard($kernel->auth()),
            methodGuard: new RouteMethodGuard(),
            csrfGuard: new CsrfGuard($kernel->csrf()),
        );
    }
}
