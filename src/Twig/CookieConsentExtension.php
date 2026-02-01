<?php

declare(strict_types=1);

namespace WSC\SuluCookieConsentBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CookieConsentExtension extends AbstractExtension
{
    public function __construct(
        private bool $enabled,
        private array $config
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sulu_cookie_consent', [$this, 'renderCookieConsent'], ['is_safe' => ['html']]),
            new TwigFunction('sulu_cookie_consent_config', [$this, 'getConfig']),
            new TwigFunction('sulu_cookie_consent_enabled', [$this, 'isEnabled']),
            new TwigFunction('wsc_cookie_consent_render', [$this, 'renderFromSnippet'], ['is_safe' => ['html']]),
        ];
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Render cookie consent from snippet content data
     */
    public function renderFromSnippet(array $snippetContent = []): string
    {
        // Merge snippet content with default config
        $config = $this->buildConfigFromSnippet($snippetContent);

        if (!($config['enabled'] ?? true)) {
            return '';
        }

        $configJson = json_encode($config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        return $this->generateHtmlOutput($configJson, $config);
    }

    public function renderCookieConsent(): string
    {
        if (!$this->enabled) {
            return '';
        }

        $configJson = json_encode($this->config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        return $this->generateHtmlOutput($configJson, $this->config);
    }

    private function buildConfigFromSnippet(array $snippet): array
    {
        return [
            'enabled' => $snippet['enabled'] ?? true,
            'title' => $snippet['title'] ?? null,
            'description' => $snippet['description'] ?? null,
            'privacy_policy_url' => $this->extractPageUrl($snippet['privacyPolicyPage'] ?? null),
            'imprint_url' => $this->extractPageUrl($snippet['imprintPage'] ?? null),
            'banner_position' => $snippet['bannerPosition'] ?? $this->config['banner_position'] ?? 'bottom',
            'banner_layout' => $snippet['bannerLayout'] ?? $this->config['banner_layout'] ?? 'box',
            'theme' => $snippet['theme'] ?? $this->config['theme'] ?? 'light',
            'flip_buttons' => $snippet['flipButtons'] ?? $this->config['flip_buttons'] ?? false,
            'equal_weight_buttons' => $snippet['equalWeightButtons'] ?? $this->config['equal_weight_buttons'] ?? true,
            'disable_page_interaction' => $snippet['disablePageInteraction'] ?? $this->config['disable_page_interaction'] ?? false,
            'cookie_expires_days' => (int) ($snippet['cookieExpiresDays'] ?? $this->config['cookie_expires_days'] ?? 365),
            'revision' => (int) ($snippet['revision'] ?? 1),
            'show_preferences_button' => $snippet['showPreferencesButton'] ?? $this->config['show_preferences_button'] ?? false,
            'preferences_button_position' => $snippet['preferencesButtonPosition'] ?? $this->config['preferences_button_position'] ?? 'bottom-left',
            'preferences_button_icon' => $snippet['preferencesButtonIcon'] ?? $this->config['preferences_button_icon'] ?? '🍪',
            'google_consent_mode' => $snippet['googleConsentMode'] ?? $this->config['google_consent_mode'] ?? false,
            'google_tag_manager_events' => $snippet['googleTagManagerEvents'] ?? $this->config['google_tag_manager_events'] ?? false,
            'matomo_tag_manager_events' => $snippet['matomoTagManagerEvents'] ?? $this->config['matomo_tag_manager_events'] ?? false,
            // Button texts from snippet
            'accept_all_btn' => $snippet['acceptAllButtonText'] ?? null,
            'accept_necessary_btn' => $snippet['acceptNecessaryButtonText'] ?? null,
            'show_preferences_btn' => $snippet['showPreferencesButtonText'] ?? null,
            'save_preferences_btn' => $snippet['savePreferencesButtonText'] ?? null,
        ];
    }

    private function extractPageUrl($page): ?string
    {
        if (is_array($page) && isset($page['url'])) {
            return $page['url'];
        }
        if (is_string($page)) {
            return $page;
        }
        return null;
    }

    private function generateHtmlOutput(string $configJson, array $config): string
    {
        $privacyUrl = json_encode($config['privacy_policy_url'] ?? '');
        $imprintUrl = json_encode($config['imprint_url'] ?? '');

        return <<<HTML
<!-- WSC Cookie Consent (Orest Bida) -->
<link rel="stylesheet" href="/bundles/wsccookieconsent/css/cookieconsent.css">
<script defer src="/bundles/wsccookieconsent/js/cookieconsent.umd.js"></script>
<script>
(function() {
    'use strict';

    const wscConfig = {$configJson};
    const privacyUrl = {$privacyUrl};
    const imprintUrl = {$imprintUrl};

    async function initCookieConsent() {
        // Wait for CookieConsent to be available
        if (typeof CookieConsent === 'undefined') {
            setTimeout(initCookieConsent, 50);
            return;
        }

        try {
            const response = await fetch('/api/cookie-consent/config');
            const data = await response.json();

            if (!data.categories || data.categories.length === 0) {
                console.warn('Cookie Consent: No categories found');
                return;
            }

            const categories = {};
            const translations = { de: { sections: [] }, en: { sections: [] } };

            // Build categories from API data
            data.categories.forEach(cat => {
                categories[cat.technicalName] = {
                    enabled: cat.enabled,
                    readOnly: cat.readOnly
                };

                // Build services for this category
                const categoryServices = {};
                data.cookies.filter(c => c.categoryTechnicalName === cat.technicalName).forEach(cookie => {
                    categoryServices[cookie.technicalName] = {
                        label: cookie.name,
                        onAccept: () => handleServiceAccept(cookie),
                        onReject: () => handleServiceReject(cookie),
                        cookies: buildCookieTable(cookie)
                    };
                });

                if (Object.keys(categoryServices).length > 0) {
                    categories[cat.technicalName].services = categoryServices;
                }

                // Add to sections for preferences modal
                translations.de.sections.push({
                    title: cat.name,
                    description: cat.description || '',
                    linkedCategory: cat.technicalName,
                    cookieTable: buildCategoryTable(data.cookies.filter(c => c.categoryTechnicalName === cat.technicalName))
                });

                translations.en.sections.push({
                    title: cat.name,
                    description: cat.description || '',
                    linkedCategory: cat.technicalName,
                    cookieTable: buildCategoryTable(data.cookies.filter(c => c.categoryTechnicalName === cat.technicalName))
                });
            });

            // Get current language
            const lang = document.documentElement.lang || 'de';

            // Build description with privacy/imprint links
            let descriptionDe = wscConfig.description || 'Wir verwenden Cookies und ähnliche Technologien auf unserer Website und verarbeiten personenbezogene Daten über dich, wie deine IP-Adresse. Wir teilen diese Daten auch mit Dritten.';
            let descriptionEn = wscConfig.description || 'We use cookies and similar technologies on our website and process personal data about you, such as your IP address. We also share this data with third parties.';

            if (privacyUrl || imprintUrl) {
                const linksDe = [];
                const linksEn = [];
                if (privacyUrl) {
                    linksDe.push('<a href="' + privacyUrl + '">Datenschutzerklärung</a>');
                    linksEn.push('<a href="' + privacyUrl + '">Privacy Policy</a>');
                }
                if (imprintUrl) {
                    linksDe.push('<a href="' + imprintUrl + '">Impressum</a>');
                    linksEn.push('<a href="' + imprintUrl + '">Imprint</a>');
                }
                descriptionDe += ' ' + linksDe.join(' | ');
                descriptionEn += ' ' + linksEn.join(' | ');
            }

            // Set translations
            translations.de.consentModal = {
                title: wscConfig.title || 'Cookie-Einstellungen',
                description: descriptionDe,
                acceptAllBtn: wscConfig.accept_all_btn || 'Alle akzeptieren',
                acceptNecessaryBtn: wscConfig.accept_necessary_btn || 'Nur Notwendige',
                showPreferencesBtn: wscConfig.show_preferences_btn || 'Einstellungen verwalten'
            };

            translations.de.preferencesModal = {
                title: 'Cookie-Einstellungen',
                acceptAllBtn: wscConfig.accept_all_btn || 'Alle akzeptieren',
                acceptNecessaryBtn: wscConfig.accept_necessary_btn || 'Nur Notwendige',
                savePreferencesBtn: wscConfig.save_preferences_btn || 'Auswahl speichern',
                closeIconLabel: 'Schließen',
                sections: translations.de.sections
            };

            translations.en.consentModal = {
                title: wscConfig.title || 'Cookie Settings',
                description: descriptionEn,
                acceptAllBtn: wscConfig.accept_all_btn || 'Accept All',
                acceptNecessaryBtn: wscConfig.accept_necessary_btn || 'Necessary Only',
                showPreferencesBtn: wscConfig.show_preferences_btn || 'Manage Preferences'
            };

            translations.en.preferencesModal = {
                title: 'Cookie Settings',
                acceptAllBtn: wscConfig.accept_all_btn || 'Accept All',
                acceptNecessaryBtn: wscConfig.accept_necessary_btn || 'Necessary Only',
                savePreferencesBtn: wscConfig.save_preferences_btn || 'Save Preferences',
                closeIconLabel: 'Close',
                sections: translations.en.sections
            };

            // Initialize CookieConsent
            CookieConsent.run({
                revision: wscConfig.revision || 1,

                guiOptions: {
                    consentModal: {
                        layout: wscConfig.banner_layout || 'box',
                        position: wscConfig.banner_position || 'bottom',
                        flipButtons: wscConfig.flip_buttons || false,
                        equalWeightButtons: wscConfig.equal_weight_buttons !== false
                    },
                    preferencesModal: {
                        layout: 'box',
                        position: 'right',
                        flipButtons: false,
                        equalWeightButtons: true
                    }
                },

                disablePageInteraction: wscConfig.disable_page_interaction || false,

                cookie: {
                    name: 'cc_cookie',
                    expiresAfterDays: wscConfig.cookie_expires_days || 365
                },

                categories: categories,

                language: {
                    default: lang,
                    autoDetect: 'document',
                    translations: translations
                },

                onFirstConsent: () => {
                    pushTagManagerEvent('cookie_consent_given');
                    updateGoogleConsentMode();
                },

                onChange: ({changedCategories, changedServices}) => {
                    pushTagManagerEvent('cookie_consent_update');
                    updateGoogleConsentMode();
                }
            });

            // Show preferences button if configured
            if (wscConfig.show_preferences_button) {
                createPreferencesButton();
            }

            // Initialize Google Consent Mode if enabled
            if (wscConfig.google_consent_mode) {
                initGoogleConsentMode();
            }

        } catch (error) {
            console.error('Cookie Consent initialization failed:', error);
        }
    }

    function buildCookieTable(cookie) {
        if (!cookie.cookieItems || cookie.cookieItems.length === 0) {
            return { headers: [], body: [] };
        }

        return {
            headers: ['Name', 'Lebensdauer', 'Beschreibung'],
            body: cookie.cookieItems.map(item => [
                item.name || '',
                item.lifetime || '-',
                item.description || ''
            ])
        };
    }

    function buildCategoryTable(cookies) {
        if (!cookies || cookies.length === 0) {
            return { headers: [], body: [] };
        }

        const rows = [];
        cookies.forEach(cookie => {
            rows.push([
                cookie.name,
                cookie.provider || '-',
                cookie.description || ''
            ]);
        });

        return {
            headers: ['Name', 'Anbieter', 'Beschreibung'],
            body: rows
        };
    }

    function handleServiceAccept(cookie) {
        // Load script if defined
        if (cookie.scriptUrl) {
            const script = document.createElement('script');
            script.src = cookie.scriptUrl;
            script.async = true;
            document.head.appendChild(script);
        }

        // Dispatch custom event
        document.dispatchEvent(new CustomEvent('cookieServiceAccept', {
            detail: { service: cookie.technicalName, category: cookie.categoryTechnicalName }
        }));

        // Push tag manager events
        if (wscConfig.google_tag_manager_events && window.dataLayer) {
            window.dataLayer.push({
                event: 'cookie_service_accept',
                cookie_service: cookie.technicalName,
                cookie_category: cookie.categoryTechnicalName
            });
        }

        if (wscConfig.matomo_tag_manager_events && window._mtm) {
            window._mtm.push({
                event: 'cookieServiceAccept',
                cookieService: cookie.technicalName,
                cookieCategory: cookie.categoryTechnicalName
            });
        }
    }

    function handleServiceReject(cookie) {
        document.dispatchEvent(new CustomEvent('cookieServiceReject', {
            detail: { service: cookie.technicalName, category: cookie.categoryTechnicalName }
        }));

        if (wscConfig.google_tag_manager_events && window.dataLayer) {
            window.dataLayer.push({
                event: 'cookie_service_reject',
                cookie_service: cookie.technicalName,
                cookie_category: cookie.categoryTechnicalName
            });
        }

        if (wscConfig.matomo_tag_manager_events && window._mtm) {
            window._mtm.push({
                event: 'cookieServiceReject',
                cookieService: cookie.technicalName,
                cookieCategory: cookie.categoryTechnicalName
            });
        }
    }

    function pushTagManagerEvent(eventName) {
        if (wscConfig.google_tag_manager_events && window.dataLayer) {
            window.dataLayer.push({ event: eventName });
        }

        if (wscConfig.matomo_tag_manager_events && window._mtm) {
            window._mtm.push({ event: eventName });
        }
    }

    function initGoogleConsentMode() {
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }

        gtag('consent', 'default', {
            'ad_storage': 'denied',
            'ad_user_data': 'denied',
            'ad_personalization': 'denied',
            'analytics_storage': 'denied',
            'functionality_storage': 'denied',
            'personalization_storage': 'denied',
            'security_storage': 'granted'
        });
    }

    function updateGoogleConsentMode() {
        if (!wscConfig.google_consent_mode) return;

        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }

        const analyticsAccepted = CookieConsent.acceptedCategory('statistics') || CookieConsent.acceptedCategory('analytics');
        const marketingAccepted = CookieConsent.acceptedCategory('marketing');
        const comfortAccepted = CookieConsent.acceptedCategory('comfort') || CookieConsent.acceptedCategory('functionality');

        gtag('consent', 'update', {
            'ad_storage': marketingAccepted ? 'granted' : 'denied',
            'ad_user_data': marketingAccepted ? 'granted' : 'denied',
            'ad_personalization': marketingAccepted ? 'granted' : 'denied',
            'analytics_storage': analyticsAccepted ? 'granted' : 'denied',
            'functionality_storage': comfortAccepted ? 'granted' : 'denied',
            'personalization_storage': comfortAccepted ? 'granted' : 'denied'
        });
    }

    function createPreferencesButton() {
        const btn = document.createElement('button');
        btn.className = 'cc-preferences-btn cc-preferences-btn--' + (wscConfig.preferences_button_position || 'bottom-left');
        btn.innerHTML = wscConfig.preferences_button_icon || '🍪';
        btn.setAttribute('aria-label', 'Cookie-Einstellungen öffnen');
        btn.onclick = () => CookieConsent.showPreferences();
        document.body.appendChild(btn);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCookieConsent);
    } else {
        initCookieConsent();
    }
})();
</script>
<style>
.cc-preferences-btn {
    position: fixed;
    z-index: 9999;
    width: 48px;
    height: 48px;
    border: none;
    border-radius: 50%;
    background: var(--cc-btn-primary-bg, #0d6efd);
    color: var(--cc-btn-primary-text, #fff);
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    transition: transform 0.2s, box-shadow 0.2s;
}
.cc-preferences-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}
.cc-preferences-btn--bottom-left { bottom: 20px; left: 20px; }
.cc-preferences-btn--bottom-right { bottom: 20px; right: 20px; }
.cc-preferences-btn--top-left { top: 20px; left: 20px; }
.cc-preferences-btn--top-right { top: 20px; right: 20px; }
</style>
<!-- /WSC Cookie Consent -->
HTML;
    }
}
