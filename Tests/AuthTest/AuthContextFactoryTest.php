<?php

declare(strict_types=1);

namespace Design\Tests\Auth;

use Design\Auth\AuthContextFactory;
use Design\Auth\Config\AuthConfig;
use Design\Auth\Resolver\LocalUserResolver;
use Design\Auth\Resolver\RoleResolver;
use Design\Auth\Resolver\SsoUserResolver;
use Design\Auth\Role\Role;
use Design\Auth\Sso\SsoSessionReader;
use Design\Auth\User\ForbiddenUser;
use Design\Auth\User\GuestUser;
use Design\Auth\User\LocalUser;
use Design\Auth\User\SsoUser;
use Design\Logging\FileLogger;
use Design\Logging\LineFormatter\SimpleLogLineFormatter;
use Design\Logging\Path\FilePathResolver;
use Design\Logging\Path\LogsDirectory;
use Design\Logging\Policy\DefaultChannelPolicy;
use Design\Logging\Context\JsonContextEncoder;
use Design\Logging\ValueObject\ChannelMap;
use Design\Tests\AuthTest\Support\InMemorySessionManager;
use Design\Tests\Support\FakeClock;
use Design\Tests\Support\InMemoryWriter;

use PHPUnit\Framework\TestCase;

final class AuthContextFactoryTest extends TestCase
{
    public function test_localhost_returns_local_user(): void
    {
        [$logger] = $this->buildLogger();

        $authConfig = new AuthConfig(
            localAuthEnabled: true,
            localForcedRole: Role::Admin,
            ssoAdminGroups: ['FR.Hive.Admin'],
            ssoPublicGroups: ['FR.Hive.Public'],
        );

        $factory = $this->buildFactory();
        $session = new InMemorySessionManager();

        $auth = $factory->create(
            authConfig: $authConfig,
            session: $session,
            logger: $logger,
            server: ['HTTP_HOST' => 'localhost'],
        );

        self::assertInstanceOf(LocalUser::class, $auth->user());
        self::assertSame(Role::Admin, $auth->role());
    }

    public function test_no_profile_returns_guest(): void
    {
        [$logger] = $this->buildLogger();

        $authConfig = new AuthConfig(
            localAuthEnabled: false,
            localForcedRole: Role::Public,
            ssoAdminGroups: ['FR.Hive.Admin'],
            ssoPublicGroups: ['FR.Hive.Public'],
        );

        $factory = $this->buildFactory();
        $session = new InMemorySessionManager();

        $auth = $factory->create($authConfig, $session, $logger, ['HTTP_HOST' => 'example.com']);

        self::assertInstanceOf(GuestUser::class, $auth->user());
        self::assertSame(Role::Guest, $auth->role());
    }

    public function test_sso_admin_group_returns_sso_admin(): void
    {
        [$logger] = $this->buildLogger();

        $authConfig = new AuthConfig(
            localAuthEnabled: false,
            localForcedRole: Role::Public,
            ssoAdminGroups: ['FR.Hive.Admin'],
            ssoPublicGroups: ['FR.Hive.Public'],
        );

        $session = new InMemorySessionManager();
        $session->set('Profile', ['id' => 'abc', 'displayName' => 'Nathan', 'mail' => 'nathan@test.com']);
        $session->set('GroupsDisplayName', ['FR.Hive.Admin' => 1]);

        $auth = $this->buildFactory()->create($authConfig, $session, $logger, ['HTTP_HOST' => 'example.com']);

        self::assertInstanceOf(SsoUser::class, $auth->user());
        self::assertSame(Role::Admin, $auth->role());
    }

    public function test_sso_public_group_returns_sso_public(): void
    {
        [$logger] = $this->buildLogger();

        $authConfig = new AuthConfig(
            localAuthEnabled: false,
            localForcedRole: Role::Public,
            ssoAdminGroups: ['FR.Hive.Admin'],
            ssoPublicGroups: ['FR.Hive.Public'],
        );

        $session = new InMemorySessionManager();
        $session->set('Profile', ['id' => 'abc', 'displayName' => 'Nathan', 'mail' => 'nathan@test.com']);
        $session->set('GroupsDisplayName', ['FR.Hive.Public' => 1]);

        $auth = $this->buildFactory()->create($authConfig, $session, $logger, ['HTTP_HOST' => 'example.com']);

        self::assertInstanceOf(SsoUser::class, $auth->user());
        self::assertSame(Role::Public, $auth->role());
    }

    public function test_sso_no_group_match_returns_forbidden(): void
    {
        [$logger] = $this->buildLogger();

        $authConfig = new AuthConfig(
            localAuthEnabled: false,
            localForcedRole: Role::Public,
            ssoAdminGroups: ['FR.Hive.Admin'],
            ssoPublicGroups: ['FR.Hive.Public'],
        );

        $session = new InMemorySessionManager();
        $session->set('Profile', ['id' => 'abc', 'displayName' => 'Nathan', 'mail' => 'nathan@test.com']);
        $session->set('GroupsDisplayName', ['SOME_OTHER_GROUP' => 1]);

        $auth = $this->buildFactory()->create($authConfig, $session, $logger, ['HTTP_HOST' => 'example.com']);

        self::assertInstanceOf(ForbiddenUser::class, $auth->user());
        self::assertSame(Role::Forbidden, $auth->role());
    }

    public function test_sso_profile_missing_id_returns_forbidden(): void
    {
        [$logger] = $this->buildLogger();

        $authConfig = new AuthConfig(
            localAuthEnabled: false,
            localForcedRole: Role::Public,
            ssoAdminGroups: ['FR.Hive.Admin'],
            ssoPublicGroups: ['FR.Hive.Public'],
        );

        $session = new InMemorySessionManager();
        $session->set('Profile', ['displayName' => 'Nathan']);
        $session->set('GroupsDisplayName', ['FR.Hive.Admin' => 1]);

        $auth = $this->buildFactory()->create($authConfig, $session, $logger, ['HTTP_HOST' => 'example.com']);

        self::assertInstanceOf(ForbiddenUser::class, $auth->user());
        self::assertSame(Role::Forbidden, $auth->role());
    }

    private function buildFactory(): AuthContextFactory
    {
        return new AuthContextFactory(
            new LocalUserResolver(),
            new SsoUserResolver(new SsoSessionReader(), new RoleResolver()),
        );
    }

    /**
     * @return array{0: FileLogger, 1: InMemoryWriter}
     */
    private function buildLogger(): array
    {
        $tmpRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php_design_auth_' . bin2hex(random_bytes(4));
        @mkdir($tmpRoot, 0775, true);

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
