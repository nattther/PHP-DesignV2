<?php

declare(strict_types=1);

namespace Design\Settings;

use Design\Session\SessionConfig;

final readonly class Settings
{
    public function __construct(
        private SessionConfig $session,
    ) {}

    public function session(): SessionConfig
    {
        return $this->session;
    }
}
