<?php

declare(strict_types=1);

namespace Design\Session\Config;


use Design\Session\Php\PhpSessionRuntime;

final class SessionConfigurator
{
    public function apply(SessionConfig $config, PhpSessionRuntime $runtime): void
    {
        if ($config->useStrictMode) {
            ini_set('session.use_strict_mode', '1');
        }

        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');

        $runtime->setCookieParams([
            'lifetime' => $config->cookieLifetime,
            'path' => $config->cookiePath,
            'domain' => $config->cookieDomain,
            'secure' => $config->cookieSecure,
            'httponly' => $config->cookieHttpOnly,
            'samesite' => $config->cookieSameSite,
        ]);
    }
}
