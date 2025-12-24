<?php

declare(strict_types=1);

namespace Design\Kernel;

use Design\Auth\AuthContext;
use Design\Http\FrontController;
use Design\Logging\LoggerInterface;
use Design\Path\ProjectPaths;
use Design\Presentation\View\AdminLayoutDecorator;
use Design\Presentation\View\CompositePhpRenderer;
use Design\Presentation\View\ViewDataFactory;
use Design\Routing\ViewPathResolver;
use Design\Security\Access\AccessChecker;
use Design\Security\Csrf\CsrfTokenManagerInterface;
use Design\Session\Flash\SessionFlashBag;
use Design\Settings\Settings;
use Design\Session\SessionManagerInterface;

final readonly class Kernel
{
    public function __construct(
        private Settings $settings,
        private LoggerInterface $logger,
        private SessionManagerInterface $session,
        private SessionFlashBag $flash,
        private CsrfTokenManagerInterface $csrf,
        private AuthContext $auth,
    ) {}

    public function settings(): Settings { return $this->settings; }
    public function logger(): LoggerInterface { return $this->logger; }
    public function session(): SessionManagerInterface { return $this->session; }
    public function flash(): SessionFlashBag { return $this->flash; }
    public function csrf(): CsrfTokenManagerInterface { return $this->csrf; }
    public function auth(): AuthContext { return $this->auth; }


     public function frontController(): FrontController
    {
        $views = $this->settings()->views();

        $resolver = new ViewPathResolver(
            $views->publicViewsRootPath(),
            $views->adminViewsRootPath(),
        );

        return new FrontController(
            kernel: $this,
            resolver: $resolver,
            renderer: new CompositePhpRenderer(),
            accessChecker: new AccessChecker($this->auth()),
            layoutDecorator: new AdminLayoutDecorator($this->auth(), $views->adminViewsRootPath()),
            viewDataFactory: new ViewDataFactory(),
        );
    }
}
