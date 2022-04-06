<?php

declare(strict_types=1);

namespace Mezzio\Authentication\Basic;

use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserRepositoryInterface;
use Psr\Container\ContainerInterface;

class BasicAccessFactory
{
    use Psr17ResponseFactoryTrait;

    public function __invoke(ContainerInterface $container): BasicAccess
    {
        /** @var UserRepositoryInterface|UserRepositoryInterface|null $userRegister */
        $userRegister = $container->has(UserRepositoryInterface::class)
            ? $container->get(UserRepositoryInterface::class)
            : ($container->has(UserRepositoryInterface::class)
                ? $container->get(UserRepositoryInterface::class)
                : null);

        if (null === $userRegister) {
            throw new Exception\InvalidConfigException(
                'UserRepositoryInterface service is missing for authentication'
            );
        }

        /** @var string|null $realm */
        $realm = $container->get('config')['authentication']['realm'] ?? null;

        if (null === $realm) {
            throw new Exception\InvalidConfigException(
                'Realm value is not present in authentication config'
            );
        }

        return new BasicAccess(
            $userRegister,
            $realm,
            $this->detectResponseFactory($container)
        );
    }
}
