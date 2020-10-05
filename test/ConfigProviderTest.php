<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-basic for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-basic/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-basic/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Authentication\Basic;

use Mezzio\Authentication\Basic\ConfigProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ConfigProviderTest extends TestCase
{
    use ProphecyTrait;

    /** @var ConfigProvider */
    private $provider;

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

    /**
     * @depends testInvocationReturnsArray
     *
     * @return void
     */
    public function testReturnedArrayContainsDependencies(array $config): void
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertIsArray($config['dependencies']);
    }

    /**
     * @depends testInvocationReturnsArray
     *
     * @return void
     */
    public function testReturnedArrayContainsAuthenticationConfig(array $config): void
    {
        $this->assertArrayHasKey('authentication', $config);
        $this->assertIsArray($config['authentication']);
    }
}
