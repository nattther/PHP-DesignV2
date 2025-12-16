<?php

declare(strict_types=1);

namespace Design\Tests\SessionTest;

use Design\Logging\FileLogger;
use Design\Logging\LineFormatter\SimpleLogLineFormatter;
use Design\Logging\Path\FilePathResolver;
use Design\Logging\Path\LogsDirectory;
use Design\Logging\Policy\DefaultChannelPolicy;
use Design\Logging\Context\JsonContextEncoder;
use Design\Logging\ValueObject\ChannelMap;
use Design\Logging\Writer\FileWriterInterface;
use Design\Session\NativeSessionManager;
use Design\Session\SessionConfig;
use Design\Session\SessionException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

final class SessionSmokeTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    #[TestDox('Test de Session + Logs — OK')]
    public function test_session_flow_and_logs_are_ok(): void
    {
        $tmpRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php_design_session_' . bin2hex(random_bytes(4));
        mkdir($tmpRoot, 0775, true);

        try {
            [$session, $writer] = $this->buildSessionWithInMemoryLogger($tmpRoot);

            // Start
            $session->start();
            self::assertTrue($session->isStarted());

            // Basic set/get/has/remove
            $session->set('user_id', 42);
            self::assertTrue($session->has('user_id'));
            self::assertSame(42, $session->get('user_id'));

            $session->remove('user_id');
            self::assertFalse($session->has('user_id'));
            self::assertSame('default', $session->get('user_id', 'default'));

            // Flash
            $session->flash('success', 'Welcome!');
            self::assertSame('Welcome!', $session->consumeFlash('success'));
            self::assertSame('none', $session->consumeFlash('success', 'none'));

            // Sensitive key should be redacted in logs (and value must never appear)
            $session->set('token', 'SECRET_TOKEN');

            // Regenerate + clear + destroy
            $session->regenerate();
            $session->set('x', 'y');
            $session->clear();
            self::assertSame([], $session->all());

            $session->destroy();
            self::assertFalse($session->isStarted());

            // Logs assertions
            self::assertNotEmpty($writer->writes);

            foreach ($writer->writes as $write) {
                self::assertStringEndsWith(DIRECTORY_SEPARATOR . 'Session.log', $write['path']);
            }

            $allLogs = implode("\n", array_map(static fn($w) => $w['content'], $writer->writes));

            self::assertStringContainsString('Session started', $allLogs);
            self::assertStringContainsString('Session key set', $allLogs);
            self::assertStringContainsString('Flash set', $allLogs);
            self::assertStringContainsString('Flash consumed', $allLogs);
            self::assertStringContainsString('Session id regenerated', $allLogs);
            self::assertStringContainsString('Session cleared', $allLogs);
            self::assertStringContainsString('Session destroyed', $allLogs);

            self::assertStringContainsString('[REDACTED]', $allLogs);
            self::assertStringNotContainsString('SECRET_TOKEN', $allLogs);
        } finally {
            $this->deleteDir($tmpRoot);
        }
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    #[TestDox('Test de Session erreurs — OK')]
    public function test_using_session_without_start_throws_and_logs_error(): void
    {
        $tmpRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php_design_session_' . bin2hex(random_bytes(4));
        mkdir($tmpRoot, 0775, true);

        try {
            [$session, $writer] = $this->buildSessionWithInMemoryLogger($tmpRoot);

            // Using session without start() must throw
            try {
                $session->get('anything');
                self::fail('Expected SessionException was not thrown.');
            } catch (SessionException $e) {
                self::assertStringContainsString('Call start() first', $e->getMessage());
            }

            // It must also log the error
            $allLogs = implode("\n", array_map(static fn($w) => $w['content'], $writer->writes));

            self::assertStringContainsString('Session not started', $allLogs);

            // And logs should still be written to Session.log
            foreach ($writer->writes as $write) {
                self::assertStringEndsWith(DIRECTORY_SEPARATOR . 'Session.log', $write['path']);
            }
        } finally {
            $this->deleteDir($tmpRoot);
        }
    }

    /**
     * Builds a NativeSessionManager + in-memory logger for tests.
     *
     * @return array{0: NativeSessionManager, 1: InMemoryWriter}
     */
    private function buildSessionWithInMemoryLogger(string $tmpRoot): array
    {
        // Logger using an in-memory writer (no real files)
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

        // Session config (unique name to avoid collisions)
        $config = new SessionConfig(
            name: 'APPSESSID_' . bin2hex(random_bytes(4)),
            cookieLifetime: 0,
            cookiePath: '/',
            cookieDomain: '',
            cookieSecure: false,
            cookieHttpOnly: true,
            cookieSameSite: 'Lax',
            useStrictMode: true,
        );

        return [new NativeSessionManager($config, $logger), $writer];
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($dir);
    }
}

/** Fake clock for predictable timestamps in logs. */
final readonly class FakeClock implements \Design\Logging\Clock\ClockInterface
{
    public function __construct(private string $iso8601) {}

    public function nowIso8601(): string
    {
        return $this->iso8601;
    }
}

/** Writer that stores writes in memory (no filesystem). */
final class InMemoryWriter implements FileWriterInterface
{
    /** @var array<int, array{path: string, content: string}> */
    public array $writes = [];

    public function append(string $filePath, string $content): void
    {
        $this->writes[] = ['path' => $filePath, 'content' => $content];
    }
}
