<?php

declare(strict_types=1);

namespace Design\Kernel;


use Design\Database\Initializer\DatabaseInitializerFactory;
use Design\Logging\LoggerFactory;
use Design\Logging\LoggerInterface;
use Design\Security\Csrf\CsrfTokenManagerFactory;
use Design\Session\Flash\SessionFlashBag;
use Design\Session\NoopSessionManager;
use Design\Session\SessionFactory;
use Design\Session\SessionManagerInterface;
use Design\Settings\Settings;
use Design\Settings\SettingsFactory;
use Design\Auth\AuthContextFactory;
use Design\Auth\Resolver\LocalUserResolver;
use Design\Auth\Resolver\RoleResolver;
use Design\Auth\Resolver\SsoUserResolver;
use Design\Auth\Sso\SsoSessionReader;

/**
 * Creates a Kernel depending on the script type (front/controller/health/cli/job).
 *
 * Goal:
 * - Keep all initialization rules in ONE place
 * - Avoid duplicating setup code in every entry file
 */
final class KernelFactory
{
    /**
     * @param array<string, mixed> $server Usually $_SERVER (pass [] for CLI)
     */
    public static function create(KernelContext $context, array $server = []): Kernel
    {
        if ($server === []) {
            $server = $_SERVER ?? [];
        }

        $settings = SettingsFactory::create(server: $server);

        $logger = self::buildLogger($settings);

        $initializer = DatabaseInitializerFactory::create(
            $settings->database(),
            $logger->channel('Database')
        );
        $initializer->initialize();

        $session = self::buildSession($context, $settings, $logger);

        if (!$session->isStarted() && !($session instanceof NoopSessionManager)) {
            $session->start();
        }

        $flash = new SessionFlashBag($session);
        $csrf  = CsrfTokenManagerFactory::create($context, $session);


        $authFactory = new AuthContextFactory(
            localUserResolver: new LocalUserResolver(),
            ssoUserResolver: new SsoUserResolver(
                reader: new SsoSessionReader(),
                roleResolver: new RoleResolver(),
            ),
        );

        $auth = $authFactory->create(
            authConfig: $settings->auth(),
            session: $session,
            logger: $logger,
            server: $server,
        );

        return new Kernel(
            settings: $settings,
            logger: $logger,
            session: $session,
            flash: $flash,
            csrf: $csrf,
            auth: $auth,
            context: $context,
        );
    }

    public static function createForFront(array $server = []): Kernel
    {
        return self::create(KernelContext::Front, $server);
    }

    public static function createForController(array $server = []): Kernel
    {
        return self::create(KernelContext::Controller, $server);
    }

    /**
     * Health is a bit special:
     * - You may want session ON (to test it)
     * - Or OFF (to keep it very lightweight)
     *
     * This version keeps session ON.
     */
    public static function createForHealth(array $server = []): Kernel
    {
        return self::create(KernelContext::Health, $server);
    }

    public static function createForCli(array $server = []): Kernel
    {
        return self::create(KernelContext::Cli, $server);
    }

    public static function createForJob(array $server = []): Kernel
    {
        return self::create(KernelContext::Job, $server);
    }

    private static function buildLogger(Settings $settings): LoggerInterface
    {
        $projectRoot = $settings->paths()->rootPath;
        return (new LoggerFactory($projectRoot))->create();
    }

    private static function buildSession(KernelContext $context, Settings $settings, LoggerInterface $logger): SessionManagerInterface
    {
        if ($context === KernelContext::Cli || $context === KernelContext::Job) {
            return new NoopSessionManager();
        }

        return (new SessionFactory($settings->session(), $logger))->create();
    }
}
