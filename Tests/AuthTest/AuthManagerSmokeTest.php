<?php

declare(strict_types=1);

namespace Design\Tests\AuthTest;

use Design\Auth\AuthManagerFactory;
use Design\Auth\AuthMode;
use Design\Tests\Support\FakeClock;
use Design\Tests\Support\InMemoryWriter;
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

final class AuthManagerSmokeTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_auth_modes_priority_and_logs(): void
    {
        $tmpRoot = sys_get_temp_dir() . '/auth_test_' . bin2hex(random_bytes(4));
        mkdir($tmpRoot, 0775, true);

        try {
            [$logger, $writer] = $this->buildLogger($tmpRoot);

            // ---------- SSO ----------
            $_SESSION['Profile'] = [
                'email' => 'john@company.com',
                'name' => 'John',
            ];
            $_SESSION['UserGroups'] = ['ADMIN'];
            $_SESSION['GroupsDisplayName'] = ['Admins'];
            $_SERVER['HTTP_HOST'] = 'localhost';

            $auth = AuthManagerFactory::create($logger)->resolve();

            self::assertTrue($auth->authenticated);
            self::assertSame(AuthMode::Sso, $auth->mode);

            // ---------- LOCAL ----------
            $_SESSION = [];
            $_SERVER['HTTP_HOST'] = 'localhost';

            $auth = AuthManagerFactory::create($logger)->resolve();

            self::assertTrue($auth->authenticated);
            self::assertSame(AuthMode::Local, $auth->mode);

            // ---------- PUBLIC ----------
            $_SESSION = [];
            $_SERVER = [];

            $auth = AuthManagerFactory::create($logger)->resolve();

            self::assertFalse($auth->authenticated);
            self::assertSame(AuthMode::Public, $auth->mode);

            // ---------- LOGS ----------
            $allLogs = implode("\n", array_map(fn ($w) => $w['content'], $writer->writes));


         
        } finally {
            $this->deleteDir($tmpRoot);
        }
    }

    private function buildLogger(string $root): array
    {
        $logsDir = LogsDirectory::fromPath($root . '/Logs');
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

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) return;

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
