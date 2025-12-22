<?php

declare(strict_types=1);

namespace Design\Auth\Sso;

final class SsoSessionReader
{
    /**
     * @return array<string, mixed>|null
     */
    public function readProfile(mixed $rawProfile): ?array
    {
        return \is_array($rawProfile) ? $rawProfile : null;
    }

    /**
     * @return array<string, int> groupName => 1
     */
    public function readGroupsDisplayName(mixed $rawGroups): array
    {
        if (!\is_array($rawGroups)) {
            return [];
        }

        $out = [];
        foreach ($rawGroups as $k => $v) {
            if (\is_string($k)) {
                $out[$k] = 1;
            }
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $profile
     */
    public function extractUserId(array $profile): string
    {
        return (string)($profile['id'] ?? '');
    }

    /**
     * @param array<string, mixed> $profile
     */
    public function extractName(array $profile): ?string
    {
        if (isset($profile['displayName'])) {
            return (string)$profile['displayName'];
        }
        if (isset($profile['name'])) {
            return (string)$profile['name'];
        }
        return null;
    }

    /**
     * @param array<string, mixed> $profile
     */
    public function extractEmail(array $profile): ?string
    {
        if (isset($profile['mail'])) {
            return (string)$profile['mail'];
        }
        if (isset($profile['email'])) {
            return (string)$profile['email'];
        }
        return null;
    }
}
