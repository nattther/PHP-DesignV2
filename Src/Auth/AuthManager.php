<?php

declare(strict_types=1);

namespace Design\Auth;

final class AuthManager
{
    /**
     * @param AuthResolverInterface[] $resolvers
     */
    public function __construct(
        private array $resolvers,
    ) {}

public function resolve(): AuthContext
{
    foreach ($this->resolvers as $resolver) {
        if (!$resolver->supports()) {
            continue;
        }

        $context = $resolver->resolve();

        if ($context !== null) {
            return $context;
        }
    }

    throw new \LogicException('No AuthResolver matched.');
}

}
