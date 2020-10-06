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

    public function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray()
    {
        $config = ($this->provider)();
        $this->assertIsArray($config);
        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config)
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertIsArray($config['dependencies']);
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsAuthenticationConfig(array $config)
    {
        $this->assertArrayHasKey('authentication', $config);
        $this->assertIsArray($config['authentication']);
    }
}
