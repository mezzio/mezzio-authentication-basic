<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-basic for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-basic/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-basic/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Authentication\Basic;

use Mezzio\Authentication\AuthenticationInterface;
use Mezzio\Authentication\Basic\BasicAccess;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BasicAccessTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<ServerRequestInterface> */
    private $request;

    /** @var ObjectProphecy<UserRepositoryInterface> */
    private $userRepository;

    /** @var ObjectProphecy<UserInterface> */
    private $authenticatedUser;

    /** @var ObjectProphecy<ResponseInterface> */
    private $responsePrototype;

    /** @var callable */
    private $responseFactory;

    protected function setUp(): void
    {
        $this->request = $this->prophesize(ServerRequestInterface::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->authenticatedUser = $this->prophesize(UserInterface::class);
        $this->responsePrototype = $this->prophesize(ResponseInterface::class);
        $this->responseFactory = $this->responseFactoryClosure();
    }

    /**
     * @return callable(): object
     */
    private function responseFactoryClosure(): callable
    {
        return function () {
            /** @var object */
            return $this->responsePrototype->reveal();
        };
    }

    public function testConstructor(): void
    {
        $basicAccess = new BasicAccess(
            $this->userRepository->reveal(),
            'test',
            $this->responseFactory
        );
        $this->assertInstanceOf(AuthenticationInterface::class, $basicAccess);
    }

    /**
     * @dataProvider provideInvalidAuthenticationHeader
     */
    public function testIsAuthenticatedWithInvalidData(array $authHeader): void
    {
        $this->request
            ->getHeader('Authorization')
            ->willReturn($authHeader);

        $this->userRepository->authenticate(Argument::any(), Argument::any())->shouldNotBeCalled();

        $basicAccess = new BasicAccess(
            $this->userRepository->reveal(),
            'test',
            $this->responseFactory
        );

        $this->assertNull($basicAccess->authenticate($this->request->reveal()));
    }

    /**
     * @dataProvider provideValidAuthentication
     */
    public function testIsAuthenticatedWithValidCredential(string $username, string $password, array $authHeader): void
    {
        $this->request
            ->getHeader('Authorization')
            ->willReturn($authHeader);
        $this->request
            ->withAttribute(UserInterface::class, Argument::type(UserInterface::class))
            ->willReturn($this->request->reveal());

        $this->authenticatedUser
            ->getIdentity()
            ->willReturn($username);
        $this->userRepository
            ->authenticate($username, $password)
            ->willReturn($this->authenticatedUser->reveal());

        $basicAccess = new BasicAccess(
            $this->userRepository->reveal(),
            'test',
            $this->responseFactory
        );

        $user = $basicAccess->authenticate($this->request->reveal());
        $this->assertInstanceOf(UserInterface::class, $user);
    }

    public function testIsAuthenticatedWithNoCredential(): void
    {
        $this->request
            ->getHeader('Authorization')
            ->willReturn(['Basic QWxhZGRpbjpPcGVuU2VzYW1l']);

        $this->userRepository
            ->authenticate('Aladdin', 'OpenSesame')
            ->willReturn(null);

        $basicAccess = new BasicAccess(
            $this->userRepository->reveal(),
            'test',
            $this->responseFactory
        );

        $this->assertNull($basicAccess->authenticate($this->request->reveal()));
    }

    public function testGetUnauthenticatedResponse(): void
    {
        $this->responsePrototype
            ->getHeader('WWW-Authenticate')
            ->willReturn(['Basic realm="test"']);
        $this->responsePrototype
            ->withHeader('WWW-Authenticate', 'Basic realm="test"')
            ->willReturn($this->responsePrototype->reveal());
        $this->responsePrototype
            ->withStatus(401)
            ->willReturn($this->responsePrototype->reveal());

        $basicAccess = new BasicAccess(
            $this->userRepository->reveal(),
            'test',
            $this->responseFactory
        );

        $response = $basicAccess->unauthorizedResponse($this->request->reveal());

        $this->assertEquals(['Basic realm="test"'], $response->getHeader('WWW-Authenticate'));
    }

    /**
     * @psalm-return array{
     * empty-header: array{0: array<empty, empty>},
     * missing-basic-prefix: array{0: array{0: string}},
     * only-username-without-colon: array{0: array{0: string}},
     * base64-encoded-pile-of-poo-emoji: array{0: array{0: string}},
     * pile-of-poo-emoji: array{0: array{0: string}},
     * only-pile-of-poo-emoji: array{0: array{0: string}},
     * basic-prefix-without-content: array{0: array{0: string}},
     * only-basic: array{0: array{0: string}},
     * multiple-auth-headers: array{0: array{0: array{0: string}, 1: array{0: string}}}
     * }
     */
    public function provideInvalidAuthenticationHeader(): array
    {
        return [
            'empty-header' => [[]],
            'missing-basic-prefix' => [['foo']],
            'only-username-without-colon' => [['Basic ' . base64_encode('Aladdin')]],
            'base64-encoded-pile-of-poo-emoji' => [['Basic ' . base64_encode('💩')]],
            'pile-of-poo-emoji' => [['Basic 💩']],
            'only-pile-of-poo-emoji' => [['💩']],
            'basic-prefix-without-content' => [['Basic ']],
            'only-basic' => [['Basic']],
            'multiple-auth-headers' => [
                [
                    ['Basic ' . base64_encode('Aladdin:OpenSesame')],
                    ['Basic ' . base64_encode('Aladdin:OpenSesame')],
                ],
            ],
        ];
    }

    /**
     * @psalm-return array{
     * aladdin: array{0: string, 1: string, 2: array{0: string}},
     * aladdin-with-nonzero-array-index: array{0: string, 1: string, 2: array{-200: string}},
     * passwords-with-colon: array{0: string, 1: string, 2: array{0: string}},
     * username-without-password: array{0: string, 1: string, 2: array{0: string}},
     * password-without-username: array{0: string, 1: string, 2: array{0: string}},
     * passwords-with-multiple-colons: array{0: string, 1: string, 2: array{0: string}},
     * no-username-or-password: array{0: string, 1: string, 2: array{0: string}},
     * no-username-password-only-colons: array{0: string, 1: string, 2: array{0: string}},
     * unicode-username-and-password: array{0: string, 1: string, 2: array{0: string}}
     * }
     */
    public function provideValidAuthentication(): array
    {
        return [
            'aladdin' => ['Aladdin', 'OpenSesame', ['Basic ' . base64_encode('Aladdin:OpenSesame')]],
            'aladdin-with-nonzero-array-index' => [
                'Aladdin',
                'OpenSesame',
                [-200 => 'Basic ' . base64_encode('Aladdin:OpenSesame')]
            ],
            'passwords-with-colon' => ['Aladdin', 'Open:Sesame', ['Basic ' . base64_encode('Aladdin:Open:Sesame')]],
            'username-without-password' => ['Aladdin', '', ['Basic ' . base64_encode('Aladdin:')]],
            'password-without-username' => ['', 'OpenSesame', ['Basic ' . base64_encode(':OpenSesame')]],
            'passwords-with-multiple-colons' => [
                'Aladdin',
                '::Open:::Sesame::',
                ['Basic ' . base64_encode('Aladdin:::Open:::Sesame::')]
            ],
            'no-username-or-password' => ['', '', ['Basic ' . base64_encode(':')]],
            'no-username-password-only-colons' => ['', '::::::', ['Basic ' . base64_encode(':::::::')]],
            'unicode-username-and-password' => [
                'thumbsup-emoji-👍',
                'thumbsdown-emoji-👎',
                ['Basic ' . base64_encode('thumbsup-emoji-👍:thumbsdown-emoji-👎')]],
        ];
    }
}
