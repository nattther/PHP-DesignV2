<?php
declare(strict_types=1);

namespace Design\Http;

use Design\Kernel\Kernel;
use Design\Presentation\View\AdminLayoutDecorator;
use Design\Presentation\View\CompositePhpRenderer;
use Design\Presentation\View\ViewDataFactory;
use Design\Routing\ViewPathResolver;
use Design\Security\Access\AccessChecker;

final class FrontControllerFactory
{
    public static function create(Kernel $kernel): FrontController
    {
        $views = $kernel->settings()->views();

        $resolver = new ViewPathResolver(
            $views->publicViewsRootPath(),
            $views->adminViewsRootPath(),
        );

        return new FrontController(
            kernel: $kernel,
            resolver: $resolver,
            renderer: new CompositePhpRenderer(),
            accessChecker: new AccessChecker($kernel->auth()),
            layoutDecorator: new AdminLayoutDecorator($kernel->auth(), $views->adminViewsRootPath()),
            viewDataFactory: new ViewDataFactory(),
        );
    }
}
