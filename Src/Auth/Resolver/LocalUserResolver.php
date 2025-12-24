<?php
declare(strict_types=1);

namespace Design\Auth\Resolver;

use Design\Auth\Config\AuthConfig;
use Design\Auth\User\LocalUser;
use Design\Auth\User\UserInterface;
use Design\Environment\EnvironmentDetector;
use Design\Logging\LoggerInterface;

final class LocalUserResolver
{
    public function __construct(
        private readonly EnvironmentDetector $environmentDetector = new EnvironmentDetector()
    ) {}

    /**
     * @param array<string, mixed> $server
     */
    public function resolve(AuthConfig $authConfig, LoggerInterface $logger, array $server): ?UserInterface
    {
        if (!$authConfig->canUseLocalAuth()) {
            return null;
        }


        if (!$this->environmentDetector->isLocalhost($server)) {
            return null;
        }

        $logger->channel('Auth')->info('Local user authenticated');

        return new LocalUser($authConfig->localRole());
    }
}
