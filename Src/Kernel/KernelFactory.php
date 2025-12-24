<?php
declare(strict_types=1);

namespace Design\Kernel;

use Design\Auth\AuthContext;
use Design\Auth\AuthContextFactory;
use Design\Auth\AuthModuleFactory;
use Design\Auth\Resolver\LocalUserResolver;
use Design\Auth\Resolver\RoleResolver;
use Design\Auth\Resolver\SsoUserResolver;
use Design\Auth\Sso\SsoSessionReader;
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

final class KernelFactory
{
    /**
     * @param array<string, mixed> $server Usually $_SERVER (pass [] for CLI)
     */
    public static function create(array $server = []): Kernel
    {
        $server = $server !== [] ? $server : ($_SERVER ?? []);

        $settings = SettingsFactory::create(server: $server);
        $logger   = self::buildLogger($settings);

        $initializer = DatabaseInitializerFactory::create(
            $settings->database(),
            $logger->channel('Database')
        );
        $initializer->initialize();

        $isHttp  = self::isHttpContext($server);
        $session = self::buildSession($isHttp, $settings, $logger);

        if ($isHttp && !$session->isStarted() && !($session instanceof NoopSessionManager)) {
            $session->start();
        }

        $flash = new SessionFlashBag($session);
        $csrf  = CsrfTokenManagerFactory::create($session);

$auth = AuthModuleFactory::createAuthContext(
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
        );
    }




    private static function isHttpContext(array $server): bool
    {
        $method = $server['REQUEST_METHOD'] ?? null;
        return is_string($method) && $method !== '';
    }

    private static function buildLogger(Settings $settings): LoggerInterface
    {
        return (new LoggerFactory($settings->paths()->rootPath))->create();
    }

    private static function buildSession(bool $isHttp, Settings $settings, LoggerInterface $logger): SessionManagerInterface
    {
        if (!$isHttp) {
            return new NoopSessionManager();
        }

        return (new SessionFactory($settings->session(), $logger))->create();
    }
}
