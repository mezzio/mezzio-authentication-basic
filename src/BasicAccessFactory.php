<?php

declare(strict_types=1);

namespace Mezzio\Authentication\Basic;

use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserRepositoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

use function is_callable;

class BasicAccessFactory
{
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

        /** @var callable|null $responseFactory */
        $responseFactory = $container->get(ResponseInterface::class) ?? null;

        if (null === $responseFactory || ! is_callable($responseFactory)) {
            throw new Exception\InvalidConfigException(
                'ResponseInterface value is not present in authentication config or not callable'
            );
        }

        return new BasicAccess(
            $userRegister,
            $realm,
            $responseFactory
        );
    }
}
