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

    public function canUseLocalAuth(): bool
    {
        return $this->localAuthEnabled;
    }

    public function localRole(): Role
    {
        return $this->localForcedRole;
    }

    public function isAdminGroup(string $group): bool
    {
        return in_array($group, $this->ssoAdminGroups, true);
    }

    public function isPublicGroup(string $group): bool
    {
        return in_array($group, $this->ssoPublicGroups, true);
    }
}
