<?php

declare(strict_types=1);

namespace MezzioTest\Authentication\Basic;

use Mezzio\Authentication\Basic\ConfigProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    private ConfigProvider $provider;

    public function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray(): array
    {
        $config = ($this->provider)();
        $this->assertIsArray($config);

        return $config;
    }

    #[Depends('testInvocationReturnsArray')]
    public function testReturnedArrayContainsDependencies(array $config): void
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertIsArray($config['dependencies']);
    }

    #[Depends('testInvocationReturnsArray')]
    public function testReturnedArrayContainsAuthenticationConfig(array $config): void
    {
        $this->assertArrayHasKey('authentication', $config);
        $this->assertIsArray($config['authentication']);
    }
}
