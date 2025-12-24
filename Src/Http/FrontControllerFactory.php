<?php
declare(strict_types=1);

namespace Design\Http;

use Design\Kernel\Kernel;
use Design\Presentation\Layout\LayoutRenderer;
use Design\Routing\LegacyDispatcher;
use Design\Security\Access\RouteAccessGuard;
use Design\Security\Access\RouteMethodGuard;
use Design\Security\Csrf\CsrfGuard;

final class FrontControllerFactory
{
    public function create(Kernel $kernel): FrontController
    {
        $appPaths = $kernel->settings()->appPaths();

        return new FrontController(
            kernel: $kernel,
            dispatcher: new LegacyDispatcher($appPaths),
            layout: new LayoutRenderer(paths: $appPaths, auth: $kernel->auth()),
            accessGuard: new RouteAccessGuard($kernel->auth()),
            methodGuard: new RouteMethodGuard(),
            csrfGuard: new CsrfGuard($kernel->csrf()),
        );
    }
}
