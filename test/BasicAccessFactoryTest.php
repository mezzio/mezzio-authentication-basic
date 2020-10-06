<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-basic for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-basic/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-basic/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Authentication\Basic;

use Mezzio\Authentication\Basic\BasicAccess;
use Mezzio\Authentication\Basic\BasicAccessFactory;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\UserRepositoryInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;

class BasicAccessFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy */
    private $container;

    /** @var BasicAccessFactory */
    private $factory;

    /** @var ObjectProphecy */
    private $userRegister;

    /** @var ObjectProphecy */
    private $responsePrototype;

    /** @var callable */
    private $responseFactory;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new BasicAccessFactory();
        $this->userRegister = $this->prophesize(UserRepositoryInterface::class);
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

    public function testInvokeWithEmptyContainer(): void
    {
        /** @var ContainerInterface $containerInterface */
        $containerInterface = $this->container->reveal();
        $this->expectException(InvalidConfigException::class);
        ($this->factory)($containerInterface);
    }

    public function testInvokeWithContainerEmptyConfig(): void
    {
        $this->container
            ->has(UserRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(UserRepositoryInterface::class)
            ->willReturn($this->userRegister->reveal());
        $this->container
            ->has(ResponseInterface::class)
            ->willReturn(true);
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn($this->responseFactory);
        $this->container
            ->get('config')
            ->willReturn([]);

        $this->expectException(InvalidConfigException::class);

        /** @var ContainerInterface $containerInterface */
        $containerInterface = $this->container->reveal();
        ($this->factory)($containerInterface);
    }

    public function testInvokeWithContainerAndConfig(): void
    {
        $this->container
            ->has(UserRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(UserRepositoryInterface::class)
            ->willReturn($this->userRegister->reveal());
        $this->container
            ->has(ResponseInterface::class)
            ->willReturn(true);
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn($this->responseFactory);
        $this->container
            ->get('config')
            ->willReturn([
                'authentication' => ['realm' => 'My page'],
            ]);

        /** @var ContainerInterface $containerInterface */
        $containerInterface = $this->container->reveal();
        $basicAccess = ($this->factory)($containerInterface);

        /** @var ResponseInterface $responseInterface */
        $responseInterface = $this->responsePrototype->reveal();
        $this->assertResponseFactoryReturns($responseInterface, $basicAccess);
    }

    public static function assertResponseFactoryReturns(ResponseInterface $expected, BasicAccess $service): void
    {
        $r = new ReflectionProperty($service, 'responseFactory');
        $r->setAccessible(true);
        /** @var callable $responseFactory */
        $responseFactory = $r->getValue($service);
        Assert::assertSame($expected, $responseFactory());
    }
}
