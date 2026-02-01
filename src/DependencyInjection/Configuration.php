<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wsc_cookie_consent');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('enabled')
                    ->defaultTrue()
                    ->info('Enable or disable the cookie consent banner')
                ->end()

                ->enumNode('banner_position')
                    ->values(['bottom-center', 'bottom-left', 'bottom-right', 'top', 'middle'])
                    ->defaultValue('bottom-center')
                    ->info('Position of the consent banner')
                ->end()

                ->enumNode('banner_layout')
                    ->values(['box', 'cloud', 'bar'])
                    ->defaultValue('box')
                    ->info('Layout style of the banner')
                ->end()

                ->enumNode('theme')
                    ->values(['light', 'dark-turquoise', 'light-funky', 'elegant-black', 'custom'])
                    ->defaultValue('light')
                    ->info('Color theme of the banner')
                ->end()

                ->integerNode('border_radius')
                    ->defaultValue(8)
                    ->min(0)
                    ->max(50)
                    ->info('Border radius in pixels')
                ->end()

                ->booleanNode('flip_buttons')
                    ->defaultFalse()
                    ->info('Flip the order of accept/reject buttons')
                ->end()

                ->booleanNode('equal_weight_buttons')
                    ->defaultTrue()
                    ->info('Give equal visual weight to all buttons')
                ->end()

                ->booleanNode('disable_page_interaction')
                    ->defaultFalse()
                    ->info('Block page interaction until consent is given')
                ->end()

                ->integerNode('cookie_expires_days')
                    ->defaultValue(365)
                    ->min(1)
                    ->max(730)
                    ->info('Number of days until the consent cookie expires')
                ->end()

                ->booleanNode('show_preferences_button')
                    ->defaultTrue()
                    ->info('Show a floating preferences button')
                ->end()

                ->enumNode('preferences_button_position')
                    ->values(['bottom-left', 'bottom-right', 'top-left', 'top-right'])
                    ->defaultValue('bottom-left')
                    ->info('Position of the preferences button')
                ->end()

                ->scalarNode('preferences_button_icon')
                    ->defaultValue('🍪')
                    ->info('Icon for the preferences button')
                ->end()

                // Custom Theme Colors
                ->scalarNode('custom_background_color')
                    ->defaultValue('#ffffff')
                    ->info('Background color for custom theme')
                ->end()

                ->scalarNode('custom_text_color')
                    ->defaultValue('#333333')
                    ->info('Text color for custom theme')
                ->end()

                ->scalarNode('custom_primary_button_bg_color')
                    ->defaultValue('#0d6efd')
                    ->info('Primary button background color for custom theme')
                ->end()

                ->scalarNode('custom_primary_button_text_color')
                    ->defaultValue('#ffffff')
                    ->info('Primary button text color for custom theme')
                ->end()

                ->scalarNode('custom_secondary_button_bg_color')
                    ->defaultValue('#eeeeee')
                    ->info('Secondary button background color for custom theme')
                ->end()

                ->scalarNode('custom_secondary_button_text_color')
                    ->defaultValue('#333333')
                    ->info('Secondary button text color for custom theme')
                ->end()

                // Tag Manager Integration
                ->booleanNode('google_consent_mode')
                    ->defaultFalse()
                    ->info('Enable Google Consent Mode v2')
                ->end()

                ->booleanNode('google_tag_manager_events')
                    ->defaultFalse()
                    ->info('Push events to Google Tag Manager dataLayer')
                ->end()

                ->booleanNode('matomo_tag_manager_events')
                    ->defaultFalse()
                    ->info('Push events to Matomo Tag Manager')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
