<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WSC\SuluCookieConsentBundle\DependencyInjection\WSCCookieConsentExtension;

class WSCCookieConsentBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new WSCCookieConsentExtension();
    }

    public function getPath(): string
    {
        return __DIR__;
    }
}
