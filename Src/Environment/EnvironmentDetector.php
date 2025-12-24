<?php
declare(strict_types=1);

namespace Design\Environment;

final class EnvironmentDetector
{
    /**
     * @param array<string, mixed> $server
     */
    public function isLocalhost(array $server): bool
    {
        $host = (string)($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? '');
        $host = strtolower((string)\preg_replace('/:\d+$/', '', $host));

        if (\in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return true;
        }

        $remote = (string)($server['REMOTE_ADDR'] ?? '');
        return \in_array($remote, ['127.0.0.1', '::1'], true);
    }
}
