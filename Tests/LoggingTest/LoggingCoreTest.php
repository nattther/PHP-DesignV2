<?php

declare(strict_types=1);

namespace Design\Tests\LoggingTest;

use Design\Logging\Exception\LogWriteException;
use Design\Logging\Policy\DefaultChannelPolicy;
use Design\Logging\ValueObject\Channel;
use Design\Logging\ValueObject\ChannelMap;
use Design\Logging\ValueObject\LogLevel;
use PHPUnit\Framework\TestCase;

final class LoggingCoreTest extends TestCase
{
    public function test_channel_sanitize_and_fallback(): void
    {
        self::assertSame('HTTPAPI', Channel::fromString('  HTTP/API  ')->name);
        self::assertSame('App', Channel::fromString('   ', 'App')->name);
        self::assertSame('App', Channel::fromString('///', 'App')->name);
    }

    public function test_channel_map_validation_and_fallback_filename(): void
    {
        $map = ChannelMap::defaults();

        self::assertSame('Payments.log', $map->fileNameFor(Channel::fromString('Payments')));

        $this->expectException(LogWriteException::class);
        ChannelMap::fromArray(['App' => '../hack.log']);
    }

    public function test_default_channel_policy(): void
    {
        $policy = DefaultChannelPolicy::standard();

        self::assertSame('Errors', $policy->choose(null, LogLevel::Error)->name);
        self::assertSame('Errors', $policy->choose(null, LogLevel::Critical)->name);
        self::assertSame('App', $policy->choose(null, LogLevel::Info)->name);

        // Requested channel always wins
        self::assertSame('Auth', $policy->choose(Channel::fromString('Auth'), LogLevel::Error)->name);
    }
}
