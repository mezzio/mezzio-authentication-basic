<?php

declare(strict_types=1);

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
            'factories' => [
                BasicAccess::class => BasicAccessFactory::class,
            ],
        ];
    }
}
