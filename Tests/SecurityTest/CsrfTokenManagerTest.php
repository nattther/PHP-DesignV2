<?php

declare(strict_types=1);

namespace Design\Tests\SecurityTest;

use Design\Security\Csrf\SessionCsrfTokenManager;
use Design\Security\Csrf\NoopCsrfTokenManager;
use Design\Session\Config\SessionConfig;
use Design\Session\Config\SessionConfigurator;
use Design\Session\NativeSessionManager;
use Design\Session\Php\PhpSessionRuntime;
use Design\Session\Security\SensitiveKeyRedactor;
use Design\Logging\FileLogger;
use Design\Logging\LineFormatter\SimpleLogLineFormatter;
use Design\Logging\Path\FilePathResolver;
use Design\Logging\Path\LogsDirectory;
use Design\Logging\Policy\DefaultChannelPolicy;
use Design\Logging\Context\JsonContextEncoder;
use Design\Logging\ValueObject\ChannelMap;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class CsrfTokenManagerTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_session_csrf_token_generation_and_validation(): void
    {
        [$session] = $this->buildSession();

        $session->start();

        $csrf = new SessionCsrfTokenManager($session);

        $token = $csrf->getToken();
        self::assertNotEmpty($token);

        // Same token returned
        self::assertSame($token, $csrf->getToken());

        // Valid token
        self::assertTrue($csrf->isValid($token));

        // Invalid token
        self::assertFalse($csrf->isValid('invalid-token'));

        // Regenerate
        $newToken = $csrf->regenerate();
        self::assertNotSame($token, $newToken);
        self::assertTrue($csrf->isValid($newToken));

        // Validate + regenerate (one-time)
        $onceToken = $csrf->getToken();
        self::assertTrue($csrf->validateAndRegenerate($onceToken));
        self::assertFalse($csrf->isValid($onceToken));
    }

    public function test_noop_csrf_never_blocks(): void
    {
        $csrf = new NoopCsrfTokenManager();

        self::assertSame('', $csrf->getToken());
        self::assertTrue($csrf->isValid('anything'));
        self::assertTrue($csrf->validateAndRegenerate('anything'));
        self::assertSame('', $csrf->regenerate());
    }

    /**
     * @return array{0: NativeSessionManager}
     */
    private function buildSession(): array
    {
        $tmpRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php_design_csrf_' . bin2hex(random_bytes(4));
        mkdir($tmpRoot, 0775, true);

        $logsDir = LogsDirectory::fromPath($tmpRoot . DIRECTORY_SEPARATOR . 'Logs');
        $resolver = new FilePathResolver($logsDir, ChannelMap::defaults());

        $logger = new FileLogger(
            clock: new \Design\Tests\SessionTest\FakeClock('2025-12-16T10:00:00+01:00'),
            pathResolver: $resolver,
            formatter: new SimpleLogLineFormatter(new JsonContextEncoder()),
            writer: new \Design\Tests\SessionTest\InMemoryWriter(),
            channelPolicy: DefaultChannelPolicy::standard(),
        );

        $config = new SessionConfig(
            name: 'CSRFSESSID_' . bin2hex(random_bytes(4)),
            cookieLifetime: 0,
            cookiePath: '/',
            cookieDomain: '',
            cookieSecure: false,
            cookieHttpOnly: true,
            cookieSameSite: 'Lax',
            useStrictMode: true,
        );

        $session = new NativeSessionManager(
            config: $config,
            logger: $logger,
            runtime: new PhpSessionRuntime(),
            configurator: new SessionConfigurator(),
            redactor: new SensitiveKeyRedactor(['csrf']),
        );

        return [$session];
    }
}
