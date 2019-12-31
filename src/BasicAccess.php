<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-basic for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-basic/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-basic/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authentication\Basic;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function base64_decode;
use function explode;
use function preg_match;
use function sprintf;

class BasicAccess implements AuthenticationInterface
{
    /**
     * @var UserRepositoryInterface
     */
    protected $repository;

    /**
     * @var string
     */
    protected $realm;

    /**
     * @var callable
     */
    protected $responseFactory;

    public function __construct(
        UserRepositoryInterface $repository,
        string $realm,
        callable $responseFactory
    ) {
        $this->repository = $repository;
        $this->realm = $realm;

        // Ensures type safety of the composed factory
        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            return $responseFactory();
        };
    }

    public function authenticate(ServerRequestInterface $request) : ?UserInterface
    {
        $authHeader = $request->getHeader('Authorization');
        if (empty($authHeader)) {
            return null;
        }

        if (! preg_match('/Basic (?P<credentials>[a-zA-Z0-9\+\/\=]+)/', $authHeader[0], $match)) {
            return null;
        }

        [$username, $password] = explode(':', base64_decode($match['credentials']));

        return $this->repository->authenticate($username, $password);
    }

    public function unauthorizedResponse(ServerRequestInterface $request) : ResponseInterface
    {
        return ($this->responseFactory)()
            ->withHeader(
                'WWW-Authenticate',
                sprintf('Basic realm="%s"', $this->realm)
            )
            ->withStatus(401);
    }
}
