<?php

declare(strict_types=1);

namespace Design\Kernel;

use Design\Auth\AuthContext;
use Design\Logging\LoggerInterface;
use Design\Path\ProjectPaths;
use Design\Security\Csrf\CsrfTokenManagerInterface;
use Design\Session\Flash\SessionFlashBag;
use Design\Settings\Settings;
use Design\Session\SessionManagerInterface;

/**
 * Kernel = the small "core" object created at the start of a script.
 *
 * It holds the shared services needed by most scripts:
 * - Settings (config + paths)
 * - Logger
 * - Session manager
 *
 * Later you can add DB, router, templating, etc.
 */
final readonly class Kernel
{
    public function __construct(
        private Settings $settings,
        private LoggerInterface $logger,
        private SessionManagerInterface $session,
        private SessionFlashBag $flash,
        private CsrfTokenManagerInterface $csrf,
        private AuthContext $auth,
        private KernelContext $context,
    ) {}

    public function context(): KernelContext
    {
        return $this->context;
    }

    public function settings(): Settings
    {
        return $this->settings;
    }

    public function paths(): ProjectPaths
    {
        return $this->settings->paths();
    }

    public function logger(): LoggerInterface
    {
        return $this->logger;
    }

    public function session(): SessionManagerInterface
    {
        return $this->session;
    }

    public function flash(): SessionFlashBag
    {
        return $this->flash;
    }

    public function csrf(): CsrfTokenManagerInterface
    {
        return $this->csrf;
    }

    public function auth(): AuthContext
    {
        return $this->auth;
    }
}
