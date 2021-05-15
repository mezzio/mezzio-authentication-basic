<?php

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

    /** @var ObjectProphecy<ContainerInterface> */
    private $container;

    /** @var BasicAccessFactory */
    private $factory;

    /** @var ObjectProphecy<UserRepositoryInterface> */
    private $userRegister;

    /** @var ObjectProphecy<ResponseInterface> */
    private $responsePrototype;

    /** @var callable */
    private $responseFactory;

    protected function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new BasicAccessFactory();
        $this->userRegister = $this->prophesize(UserRepositoryInterface::class);
        $this->responsePrototype = $this->prophesize(ResponseInterface::class);
        $this->responseFactory = function (): ResponseInterface {
            return $this->responsePrototype->reveal();
        };
    }

    public function testInvokeWithEmptyContainer(): void
    {
        $this->expectException(InvalidConfigException::class);
        ($this->factory)($this->container->reveal());
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
        ($this->factory)($this->container->reveal());
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

        $basicAccess = ($this->factory)($this->container->reveal());
        $this->assertResponseFactoryReturns($this->responsePrototype->reveal(), $basicAccess);
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
