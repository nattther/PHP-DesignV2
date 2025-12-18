<?php

declare(strict_types=1);

namespace Design\Session\Php;

final class PhpSessionRuntime
{
    public function headersSent(): bool { return headers_sent(); }

    public function status(): int { return session_status(); }

    public function start(): bool { return session_start(); }

    public function destroy(): bool { return session_destroy(); }

    public function regenerateId(bool $deleteOldSession): bool { return session_regenerate_id($deleteOldSession); }

    public function id(): string { return session_id(); }

    public function name(): string { return session_name(); }

    public function setName(string $name): void { session_name($name); }

    public function setCookieParams(array $params): void { session_set_cookie_params($params); }
}
