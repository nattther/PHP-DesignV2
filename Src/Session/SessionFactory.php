<?php

declare(strict_types=1);

namespace Design\Session;

use Design\Logging\LoggerInterface;
use Design\Session\Config\SessionConfigurator;
use Design\Session\Config\SessionConfig;
use Design\Session\Php\PhpSessionRuntime;
use Design\Session\Security\SensitiveKeyRedactor;

final readonly class SessionFactory
{
    /**
     * @param array<int, string> $sensitiveKeys Keys that should be hidden in logs
     */
    public function __construct(
        private SessionConfig $config,
        private LoggerInterface $logger,
        private array $sensitiveKeys = ['password', 'token', 'csrf', 'auth', 'jwt'],
    ) {}

    public function create(): NativeSessionManager
    {
        return new NativeSessionManager(
            config: $this->config,
            logger: $this->logger,
            runtime: new PhpSessionRuntime(),
            configurator: new SessionConfigurator(),
            redactor: new SensitiveKeyRedactor($this->sensitiveKeys),
        );
    }
}
