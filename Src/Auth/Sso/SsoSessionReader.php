<?php
declare(strict_types=1);

namespace Design\Auth\Sso;

use Design\Session\SessionManagerInterface;

final class SsoSessionReader
{
    private const KEY_PROFILE = 'Profile';
    private const KEY_GROUPS  = 'GroupsDisplayName';

    /**
     * @return array<string, mixed>|null
     */
    public function readProfileFromSession(SessionManagerInterface $session): ?array
    {
        return $this->readProfile($session->get(self::KEY_PROFILE));
    }

    /**
     * @return array<string, int>
     */
    public function readGroupsFromSession(SessionManagerInterface $session): array
    {
        return $this->readGroupsDisplayName($session->get(self::KEY_GROUPS));
    }

    /**
     * @param array<string, mixed> $profile
     */
    public function extractIdentity(array $profile): SsoIdentity
    {
        return new SsoIdentity(
            id: $this->extractUserId($profile),
            name: $this->extractName($profile),
            email: $this->extractEmail($profile),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readProfile(mixed $rawProfile): ?array
    {
        return \is_array($rawProfile) ? $rawProfile : null;
    }

    /**
     * @return array<string, int> groupName => 1
     */
    private function readGroupsDisplayName(mixed $rawGroups): array
    {
        if (!\is_array($rawGroups)) {
            return [];
        }

        $out = [];
        foreach ($rawGroups as $k => $_) {
            if (\is_string($k) && $k !== '') {
                $out[$k] = 1;
            }
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function extractUserId(array $profile): string
    {
        return (string)($profile['id'] ?? '');
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function extractName(array $profile): ?string
    {
        if (isset($profile['displayName'])) {
            return (string) $profile['displayName'];
        }
        if (isset($profile['name'])) {
            return (string) $profile['name'];
        }
        return null;
    }

    /**
     * @param array<string, mixed> $profile
     */
    private function extractEmail(array $profile): ?string
    {
        if (isset($profile['mail'])) {
            return (string) $profile['mail'];
        }
        if (isset($profile['email'])) {
            return (string) $profile['email'];
        }
        return null;
    }
}
