<?php

declare(strict_types=1);

namespace Design\Session\Config;

/**
 * Immutable session configuration used by the SessionManager.
 *
 * This class contains ONLY values.
 * Creation of default values is handled by SettingsFactory (central place).
 */
final readonly class SessionConfig
{
    public function __construct(
        public string $name,
        public int $cookieLifetime,
        public string $cookiePath,
        public string $cookieDomain,
        public bool $cookieSecure,
        public bool $cookieHttpOnly,
        public string $cookieSameSite,
        public bool $useStrictMode,
    ) {}
}
