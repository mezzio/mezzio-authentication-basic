<?php

namespace Mezzio\Authentication\Basic;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'authentication' => $this->getAuthenticationConfig(),
            'dependencies'   => $this->getDependencies(),
        ];
    }

    public function getAuthenticationConfig(): array
    {
        return [
            'realm' => '', // Provide the realm string
        ];
    }

    public function getDependencies(): array
    {
        return [
            // Legacy Zend Framework aliases
            'aliases'   => [
                \Zend\Expressive\Authentication\Basic\BasicAccess::class => BasicAccess::class,
            ],
            'factories' => [
                BasicAccess::class => BasicAccessFactory::class,
            ],
        ];
    }
}
