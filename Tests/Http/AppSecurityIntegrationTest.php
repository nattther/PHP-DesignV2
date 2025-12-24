<?php
declare(strict_types=1);

namespace Design\Tests\Http;

use Design\Auth\AuthContext;
use Design\Auth\Config\AuthConfig;
use Design\Auth\Role\Role;
use Design\Auth\User\LocalUser;
use Design\Database\Config\DatabaseConfig;
use Design\Database\Config\DatabasePaths;
use Design\Error\ErrorType\AccessDeniedMapper;
use Design\Error\ErrorType\BadRouteMapper;
use Design\Error\ErrorType\CsrfInvalidMapper;
use Design\Error\ErrorType\Default500Mapper;
use Design\Error\ErrorType\MethodNotAllowedMapper;
use Design\Error\ErrorType\RouteNotFoundMapper;
use Design\Error\Handler\HttpErrorHandler;
use Design\Http\FrontController;
use Design\Kernel\Kernel;
use Design\Logging\LoggerInterface;
use Design\Path\AppPaths;
use Design\Path\ProjectPaths;
use Design\Presentation\Layout\LayoutRenderer;
use Design\Routing\LegacyDispatcher;
use Design\Security\Access\RouteAccessGuard;
use Design\Security\Access\RouteMethodGuard;
use Design\Security\Csrf\CsrfGuard;
use Design\Security\Csrf\SessionCsrfTokenManager;
use Design\Session\Config\SessionConfig;
use Design\Session\Flash\SessionFlashBag;
use Design\Settings\Settings;
use Design\Tests\Support\CapturingErrorRenderer;
use Design\Tests\Support\FakeRequestFactory;
use Design\Tests\Support\InMemorySessionManager;
use PHPUnit\Framework\TestCase;

use Design\Logging\FileLogger;
use Design\Logging\LineFormatter\SimpleLogLineFormatter;
use Design\Logging\Path\FilePathResolver;
use Design\Logging\Path\LogsDirectory;
use Design\Logging\Policy\DefaultChannelPolicy;
use Design\Logging\Context\JsonContextEncoder;
use Design\Logging\ValueObject\ChannelMap;
use Design\Tests\Support\FakeClock;
use Design\Tests\Support\InMemoryWriter;



final class AppSecurityIntegrationTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir() . '/design_it_' . bin2hex(random_bytes(4));

        @mkdir($this->tmp . '/public/public_views', 0775, true);
        @mkdir($this->tmp . '/public/admin_views', 0775, true);
        @mkdir($this->tmp . '/ajax', 0775, true);
        @mkdir($this->tmp . '/controller', 0775, true);

        // Layout parts
        file_put_contents($this->tmp . '/public/public_views/_navbar.php', '<?php echo "NAV_PUBLIC|";');
        file_put_contents($this->tmp . '/public/public_views/_footer.php', '<?php echo "|FOOT_PUBLIC";');

        file_put_contents($this->tmp . '/public/admin_views/_navbar.php', '<?php echo "NAV_ADMIN|";');
        file_put_contents($this->tmp . '/public/admin_views/_footer.php', '<?php echo "|FOOT_ADMIN";');

        // Views
        file_put_contents($this->tmp . '/public/public_views/home.php', '<?php echo "HOME";');
        file_put_contents($this->tmp . '/public/admin_views/dashboard.php', '<?php echo "DASH";');

        // Ajax + action scripts
        file_put_contents($this->tmp . '/ajax/ping.php', '<?php echo "PONG";');
        file_put_contents($this->tmp . '/controller/do.php', '<?php echo "DID";');
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->tmp);
    }

    public function test_public_page_renders_public_layout(): void
    {
        [$front, $handler, $renderer] = $this->makeStack(Role::Public);

        $req = FakeRequestFactory::make(get: ['page' => 'home'], server: ['REQUEST_METHOD' => 'GET']);

        ob_start();
        $handler->handle(fn() => $front->handle($req));
        $out = (string) ob_get_clean();

        self::assertNull($renderer->status, 'No error expected.');
        self::assertSame('NAV_PUBLIC|HOME|FOOT_PUBLIC', $out);
    }

    public function test_admin_page_as_public_is_403(): void
    {
        [$front, $handler, $renderer] = $this->makeStack(Role::Public);

        $req = FakeRequestFactory::make(get: ['admin_page' => 'dashboard'], server: ['REQUEST_METHOD' => 'GET']);

        ob_start();
        $handler->handle(fn() => $front->handle($req));
        ob_end_clean();

        self::assertSame(403, $renderer->status);
    }

    public function test_admin_page_as_admin_renders_admin_layout(): void
    {
        [$front, $handler, $renderer] = $this->makeStack(Role::Admin);

        $req = FakeRequestFactory::make(get: ['admin_page' => 'dashboard'], server: ['REQUEST_METHOD' => 'GET']);

        ob_start();
        $handler->handle(fn() => $front->handle($req));
        $out = (string) ob_get_clean();

        self::assertNull($renderer->status);
        self::assertSame('NAV_ADMIN|DASH|FOOT_ADMIN', $out);
    }

    public function test_action_is_get_only_post_is_405(): void
    {
        [$front, $handler, $renderer] = $this->makeStack(Role::Public);

        $req = FakeRequestFactory::make(get: ['action' => 'do'], server: ['REQUEST_METHOD' => 'POST']);

        ob_start();
        $handler->handle(fn() => $front->handle($req));
        ob_end_clean();

        self::assertSame(405, $renderer->status);
    }

    public function test_ajax_post_without_csrf_is_419(): void
    {
        [$front, $handler, $renderer] = $this->makeStack(Role::Public);

        $req = FakeRequestFactory::make(post: ['ajax' => 'ping'], server: ['REQUEST_METHOD' => 'POST']);

        ob_start();
        $handler->handle(fn() => $front->handle($req));
        ob_end_clean();

        self::assertSame(419, $renderer->status);
    }

    public function test_ajax_post_with_valid_csrf_runs_and_rotates_token(): void
    {
        [$front, $handler, $renderer, $csrfManager] = $this->makeStack(Role::Public, true);

        $token1 = $csrfManager->getToken();

        $req = FakeRequestFactory::make(
            post: ['ajax' => 'ping', '_csrf' => $token1],
            server: ['REQUEST_METHOD' => 'POST']
        );

        ob_start();
        $handler->handle(fn() => $front->handle($req));
        $out = (string) ob_get_clean();

        self::assertNull($renderer->status);
        self::assertSame('PONG', $out);

        $token2 = $csrfManager->getToken();
        self::assertNotSame($token1, $token2, 'CSRF token should rotate after valid POST.');
    }

    /**
     * @return array{0:FrontController,1:HttpErrorHandler,2:CapturingErrorRenderer,3?:SessionCsrfTokenManager}
     */
    private function makeStack(Role $role, bool $returnCsrf = false): array
    {
        $projectPaths = new ProjectPaths(
            rootPath: $this->tmp,
            publicPath: $this->tmp . '/public',
            logsPath: $this->tmp . '/Logs',
            baseUrl: '',
        );

        $appPaths = new AppPaths($projectPaths);

        $settings = new Settings(
            paths: $projectPaths,
            appPaths: $appPaths,
            session: new SessionConfig(
                name: 'TESTSESSID_' . bin2hex(random_bytes(4)),
                cookieLifetime: 0,
                cookiePath: '/',
                cookieDomain: '',
                cookieSecure: false,
                cookieHttpOnly: true,
                cookieSameSite: 'Lax',
                useStrictMode: true,
            ),
            database: new DatabaseConfig(driver: 'sqlite', projectName: 'test', sqliteFileName: 'test.sqlite'),
            databasePaths: new DatabasePaths(settingsDirPath: $this->tmp . '/Settings', projectName: 'test', sqliteFileName: 'test.sqlite'),
            auth: new AuthConfig(
                localAuthEnabled: true,
                localForcedRole: $role,
                ssoAdminGroups: ['ADMIN'],
                ssoPublicGroups: ['PUBLIC'],
            ),
        );

        [$logger, $writer] = $this->buildInMemoryLogger($this->tmp);

        $session = new InMemorySessionManager(started: true);
        $flash = new SessionFlashBag($session);

        $csrfManager = new SessionCsrfTokenManager($session);
        $auth = new AuthContext(new LocalUser($role));

        $kernel = new Kernel(
            settings: $settings,
            logger: $logger,
            session: $session,
            flash: $flash,
            csrf: $csrfManager,
            auth: $auth,
        );

        $front = new FrontController(
            kernel: $kernel,
            dispatcher: new LegacyDispatcher($appPaths),
            layout: new LayoutRenderer(paths: $appPaths, auth: $auth),
            accessGuard: new RouteAccessGuard($auth),
            methodGuard: new RouteMethodGuard(),
            csrfGuard: new CsrfGuard($csrfManager),
        );

        $renderer = new CapturingErrorRenderer();

        $handler = new HttpErrorHandler(
            renderer: $renderer,
            logger: $logger,
            mappers: [
                new BadRouteMapper(),
                new RouteNotFoundMapper(),
                new AccessDeniedMapper(),
                new MethodNotAllowedMapper(),
                new CsrfInvalidMapper(),
                new Default500Mapper(),
            ]
        );

        $result = [$front, $handler, $renderer];
        if ($returnCsrf) { $result[] = $csrfManager; }

        return $result;
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) return;

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($it as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($dir);
    }

    /**
 * @return array{0: LoggerInterface, 1: InMemoryWriter}
 */
private function buildInMemoryLogger(string $tmpRoot): array
{
    $logsDir = LogsDirectory::fromPath($tmpRoot . DIRECTORY_SEPARATOR . 'Logs');
    $resolver = new FilePathResolver($logsDir, ChannelMap::defaults());

    $writer = new InMemoryWriter();

    $logger = new FileLogger(
        clock: new FakeClock('2025-12-16T10:00:00+01:00'),
        pathResolver: $resolver,
        formatter: new SimpleLogLineFormatter(new JsonContextEncoder()),
        writer: $writer,
        channelPolicy: DefaultChannelPolicy::standard(),
    );

    return [$logger, $writer];
}

}
