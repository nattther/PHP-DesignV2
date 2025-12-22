<?php

declare(strict_types=1);

namespace Design\Auth\Resolver;

use Design\Auth\Config\AuthConfig;
use Design\Auth\User\LocalUser;
use Design\Auth\User\UserInterface;
use Design\Logging\LoggerInterface;

final class LocalUserResolver
{
    public function resolve(AuthConfig $authConfig, LoggerInterface $logger, array $server): ?UserInterface
    {
        if (!$authConfig->localAuthEnabled) {
            return null;
        }

        if (!$this->isLocalhost($server)) {
            return null;
        }

        $role = $authConfig->localForcedRole;

        return new LocalUser($role);
    }

    private function isLocalhost(array $server): bool
    {
        $host = (string)($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? '');
        $host = strtolower((string)\preg_replace('/:\d+$/', '', $host));


        if (\in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }

        $remote = (string)($server['REMOTE_ADDR'] ?? '');
        if (\in_array($remote, ['127.0.0.1', '::1'], true)) {
            return true;
        }

        return false;
    }
}
