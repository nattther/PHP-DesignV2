<?php

declare(strict_types=1);

namespace Design\Auth\Config;

use Design\Auth\Role\Role;

final readonly class AuthConfig
{
    /**
     * @param list<string> $ssoAdminGroups
     * @param list<string> $ssoPublicGroups
     */
    public function __construct(
        public bool $localAuthEnabled,
        public Role $localForcedRole,
        public array $ssoAdminGroups,
        public array $ssoPublicGroups,
    ) {}
}
