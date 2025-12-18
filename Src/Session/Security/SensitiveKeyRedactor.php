<?php

declare(strict_types=1);

namespace Design\Session\Security;

final class SensitiveKeyRedactor
{
    /** @var array<string, true> */
    private array $sensitiveKeys;

    /**
     * @param array<int, string> $sensitiveKeys
     */
    public function __construct(array $sensitiveKeys)
    {
        $this->sensitiveKeys = [];
        foreach ($sensitiveKeys as $k) {
            $this->sensitiveKeys[(string) $k] = true;
        }
    }

    public function redact(string $key): string
    {
        return isset($this->sensitiveKeys[$key]) ? '[REDACTED]' : $key;
    }
}
