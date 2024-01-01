<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\Basic;

use Mezzio\Authentication\Basic\BasicAccess;
use Mezzio\Authentication\Basic\BasicAccessFactory;
use Mezzio\Authentication\Exception\InvalidConfigException;
use Mezzio\Authentication\UserRepositoryInterface;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;

class BasicAccessFactoryTest extends TestCase
{
    /** @var MockObject&UserRepositoryInterface */
    private $userRegister;

    /** @var MockObject&ResponseInterface */
    private $responsePrototype;

    /** @var callable */
    private $responseFactory;

    protected function setUp(): void
    {
        $this->userRegister      = $this->createMock(UserRepositoryInterface::class);
        $this->responsePrototype = $this->createMock(ResponseInterface::class);
        $this->responseFactory   = fn(): ResponseInterface => $this->responsePrototype;
    }

    public function testInvokeWithEmptyContainer(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory   = new BasicAccessFactory();
        $this->expectException(InvalidConfigException::class);
        $factory($container);
    }

    public function testInvokeWithContainerEmptyConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->atLeastOnce())
            ->method('has')
            ->willReturnMap([
                [UserRepositoryInterface::class, true],
                [ResponseFactoryInterface::class, false],
            ]);
        $container->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap([
                [UserRepositoryInterface::class, $this->userRegister],
                [ResponseInterface::class, $this->responseFactory],
                ['config', []],
            ]);

        $factory = new BasicAccessFactory();

        $this->expectException(InvalidConfigException::class);
        $factory($container);
    }

    public function testInvokeWithContainerAndConfig(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->atLeastOnce())
            ->method('has')
            ->willReturnMap([
                [UserRepositoryInterface::class, true],
                [ResponseFactoryInterface::class, false],
                [ResponseInterface::class, true],
            ]);
        $container->expects($this->atLeastOnce())
            ->method('get')
            ->willReturnMap([
                [UserRepositoryInterface::class, $this->userRegister],
                [ResponseInterface::class, $this->responseFactory],
                [
                    'config',
                    [
                        'authentication' => ['realm' => 'My page'],
                    ],
                ],
            ]);

        $factory     = new BasicAccessFactory();
        $basicAccess = $factory($container);
        $this->assertResponseFactoryReturns($this->responsePrototype, $basicAccess);
    }

    public function assertResponseFactoryReturns(ResponseInterface $expected, BasicAccess $service): void
    {
        $r = new ReflectionProperty($service, 'responseFactory');
        /** @var ResponseFactoryInterface $responseFactory */
        $responseFactory = $r->getValue($service);
        $this->responsePrototype
            ->expects($this->once())
            ->method('withStatus')
            ->with(200, '')
            ->willReturnSelf();
        Assert::assertSame($expected, $responseFactory->createResponse());
    }
}
