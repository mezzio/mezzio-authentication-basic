<?php

/**
 * @see       https://github.com/mezzio/mezzio-authentication-basic for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authentication-basic/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authentication-basic/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authentication\Basic;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'authentication' => $this->getAuthenticationConfig(),
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getAuthenticationConfig() : array
    {
        return [
            'realm' => '', // Provide the realm string
        ];
    }

    public function getDependencies() : array
    {
        return [
            // Legacy Zend Framework aliases
            'aliases' => [
                \Zend\Expressive\Authentication\Basic\BasicAccess::class => BasicAccess::class,
            ],
            'factories' => [
                BasicAccess::class => BasicAccessFactory::class,
            ],
        ];
    }
}
