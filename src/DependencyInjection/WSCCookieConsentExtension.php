<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class WSCCookieConsentExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Store configuration as parameters
        foreach ($config as $key => $value) {
            $container->setParameter('wsc_cookie_consent.' . $key, $value);
        }

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('doctrine')) {
            $container->prependExtensionConfig('doctrine', [
                'orm' => [
                    'mappings' => [
                        'WSCCookieConsentBundle' => [
                            'type' => 'attribute',
                            'dir' => __DIR__ . '/../Entity',
                            'prefix' => 'WSC\SuluCookieConsentBundle\Entity',
                            'alias' => 'WSCCookieConsentBundle',
                            'is_bundle' => false,
                        ],
                    ],
                ],
            ]);
        }

        if ($container->hasExtension('sulu_admin')) {
            $container->prependExtensionConfig('sulu_admin', [
                'lists' => [
                    'directories' => [
                        __DIR__ . '/../Resources/config/lists',
                    ],
                ],
                'forms' => [
                    'directories' => [
                        __DIR__ . '/../Resources/config/forms',
                    ],
                ],
                'resources' => [
                    'cookie_categories' => [
                        'routes' => [
                            'list' => 'wsc_cookie_consent.get_cookie_categories',
                            'detail' => 'wsc_cookie_consent.get_cookie_category',
                        ],
                    ],
                    'cookies' => [
                        'routes' => [
                            'list' => 'wsc_cookie_consent.get_cookies',
                            'detail' => 'wsc_cookie_consent.get_cookie',
                        ],
                    ],
                ],
            ]);
        }
    }

    public function getAlias(): string
    {
        return 'wsc_cookie_consent';
    }
}
