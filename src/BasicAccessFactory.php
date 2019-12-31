<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-basic for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-basic/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-basic/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authentication\Basic;

use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserRepositoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class BasicAccessFactory
{
    public function __invoke(ContainerInterface $container) : BasicAccess
    {
        $userRegister = $container->has(UserRepositoryInterface::class)
            ? $container->get(UserRepositoryInterface::class)
            : ($container->has(\Zend\Expressive\Authentication\UserRepositoryInterface::class)
                ? $container->get(\Zend\Expressive\Authentication\UserRepositoryInterface::class)
                : null);

        if (null === $userRegister) {
            throw new Exception\InvalidConfigException(
                'UserRepositoryInterface service is missing for authentication'
            );
        }

        $realm = $container->get('config')['authentication']['realm'] ?? null;

        if (null === $realm) {
            throw new Exception\InvalidConfigException(
                'Realm value is not present in authentication config'
            );
        }

        return new BasicAccess(
            $userRegister,
            $realm,
            $container->get(ResponseInterface::class)
        );
    }
}
