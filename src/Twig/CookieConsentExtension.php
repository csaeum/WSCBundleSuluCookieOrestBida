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

    public function renderCookieConsent(): string
    {
        if (!$this->enabled) {
            return '';
        }

        $configJson = json_encode($this->config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

        return <<<HTML
<!-- WSC Cookie Consent -->
<link rel="stylesheet" href="/bundles/wsccookieconsent/css/cookieconsent.css">
<script src="/bundles/wsccookieconsent/js/cookieconsent.umd.js"></script>
<script>
(function() {
    'use strict';

    const wscConfig = {$configJson};

    async function initCookieConsent() {
        try {
            const response = await fetch('/api/cookie-consent/config');
            const data = await response.json();

            if (!data.config || !data.config.enabled) {
                return;
            }

            const categories = {};
            const services = {};

            // Build categories from API data
            data.categories.forEach(cat => {
                categories[cat.technicalName] = {
                    enabled: cat.enabled,
                    readOnly: cat.readOnly
                };
            });

            // Group cookies by category
            const cookiesByCategory = {};
            data.cookies.forEach(cookie => {
                if (!cookiesByCategory[cookie.categoryTechnicalName]) {
                    cookiesByCategory[cookie.categoryTechnicalName] = [];
                }
                cookiesByCategory[cookie.categoryTechnicalName].push(cookie);
            });

            // Build services for each category
            Object.keys(cookiesByCategory).forEach(categoryName => {
                services[categoryName] = {};
                cookiesByCategory[categoryName].forEach(cookie => {
                    services[categoryName][cookie.technicalName] = {
                        label: buildServiceLabel(cookie),
                        onAccept: () => handleServiceAccept(cookie, data.config),
                        onReject: () => handleServiceReject(cookie, data.config)
                    };
                });
            });

            // Get current language
            const lang = document.documentElement.lang || 'de';

            // Initialize CookieConsent
            CookieConsent.run({
                revision: parseInt(data.revision, 16),

                guiOptions: {
                    consentModal: {
                        layout: data.config.banner_layout || 'box',
                        position: data.config.banner_position || 'bottom center',
                        flipButtons: data.config.flip_buttons || false,
                        equalWeightButtons: data.config.equal_weight_buttons !== false
                    },
                    preferencesModal: {
                        layout: 'box',
                        position: 'right',
                        flipButtons: false,
                        equalWeightButtons: true
                    }
                },

                categories: categories,
                services: services,

                language: {
                    default: lang,
                    autoDetect: 'document',
                    translations: {
                        de: {
                            consentModal: {
                                title: 'Cookie-Einstellungen',
                                description: 'Wir verwenden Cookies und ähnliche Technologien auf unserer Website und verarbeiten personenbezogene Daten von dir (z.B. IP-Adresse), um z.B. Inhalte und Anzeigen zu personalisieren, Medien von Drittanbietern einzubinden oder Zugriffe auf unsere Website zu analysieren.',
                                acceptAllBtn: 'Alle akzeptieren',
                                acceptNecessaryBtn: 'Nur Notwendige',
                                showPreferencesBtn: 'Einstellungen verwalten'
                            },
                            preferencesModal: {
                                title: 'Cookie-Einstellungen',
                                acceptAllBtn: 'Alle akzeptieren',
                                acceptNecessaryBtn: 'Nur Notwendige',
                                savePreferencesBtn: 'Auswahl speichern',
                                closeIconLabel: 'Schließen',
                                sections: buildPreferencesSections(data.categories, lang)
                            }
                        },
                        en: {
                            consentModal: {
                                title: 'Cookie Settings',
                                description: 'We use cookies and similar technologies on our website and process personal data about you (e.g. IP address), for example, to personalize content and ads, to integrate media from third-party providers or to analyze access to our website.',
                                acceptAllBtn: 'Accept All',
                                acceptNecessaryBtn: 'Necessary Only',
                                showPreferencesBtn: 'Manage Preferences'
                            },
                            preferencesModal: {
                                title: 'Cookie Settings',
                                acceptAllBtn: 'Accept All',
                                acceptNecessaryBtn: 'Necessary Only',
                                savePreferencesBtn: 'Save Preferences',
                                closeIconLabel: 'Close',
                                sections: buildPreferencesSections(data.categories, lang)
                            }
                        }
                    }
                },

                onFirstConsent: () => {
                    pushTagManagerEvent('cookie_consent_given', data.config);
                },

                onChange: ({changedCategories, changedServices}) => {
                    pushTagManagerEvent('cookie_consent_update', data.config);
                    updateGoogleConsentMode(data.config);
                },

                onModalShow: () => {},
                onModalHide: () => {}
            });

            // Show preferences button if configured
            if (data.config.show_preferences_button) {
                createPreferencesButton(data.config);
            }

            // Initialize Google Consent Mode if enabled
            if (data.config.google_consent_mode) {
                initGoogleConsentMode();
            }

        } catch (error) {
            console.error('Cookie Consent initialization failed:', error);
        }
    }

    function buildServiceLabel(cookie) {
        let html = '<div class="cc-service-info">';

        if (cookie.description) {
            html += '<p class="cc-service-desc">' + escapeHtml(cookie.description) + '</p>';
        }

        html += '<ul class="cc-service-details">';

        if (cookie.provider) {
            html += '<li><strong>Anbieter:</strong> ' + escapeHtml(cookie.provider) + '</li>';
        }

        if (cookie.dataCollected) {
            html += '<li><strong>Erhobene Daten:</strong> ' + escapeHtml(cookie.dataCollected) + '</li>';
        }

        if (cookie.dataPurpose) {
            html += '<li><strong>Zweck:</strong> ' + escapeHtml(cookie.dataPurpose) + '</li>';
        }

        const legalBasisLabels = {
            'consent': 'Einwilligung',
            'legitimate_interest': 'Berechtigtes Interesse',
            'contract': 'Vertragserfüllung',
            'legal_obligation': 'Rechtliche Verpflichtung'
        };
        if (cookie.legalBasis && legalBasisLabels[cookie.legalBasis]) {
            html += '<li><strong>Rechtsgrundlage:</strong> ' + legalBasisLabels[cookie.legalBasis] + '</li>';
        }

        const locationLabels = {
            'germany': 'Deutschland',
            'eu': 'EU/EWR',
            'usa': 'USA',
            'worldwide': 'Weltweit'
        };
        if (cookie.processingLocation && locationLabels[cookie.processingLocation]) {
            html += '<li><strong>Verarbeitungsort:</strong> ' + locationLabels[cookie.processingLocation] + '</li>';
        }

        if (cookie.privacyPolicyUrl) {
            html += '<li><a href="' + escapeHtml(cookie.privacyPolicyUrl) + '" target="_blank" rel="noopener">Datenschutzerklärung</a></li>';
        }

        html += '</ul>';

        // Cookie table
        if (cookie.cookieItems && cookie.cookieItems.length > 0) {
            html += '<table class="cc-cookie-table"><thead><tr><th>Cookie</th><th>Lebensdauer</th><th>Beschreibung</th></tr></thead><tbody>';
            cookie.cookieItems.forEach(item => {
                html += '<tr>';
                html += '<td>' + escapeHtml(item.name) + '</td>';
                html += '<td>' + escapeHtml(item.lifetime || '-') + '</td>';
                html += '<td>' + escapeHtml(item.description || '-') + '</td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
        }

        html += '</div>';
        return html;
    }

    function buildPreferencesSections(categories, lang) {
        const sections = [];

        categories.forEach(cat => {
            sections.push({
                title: cat.name,
                description: cat.description,
                linkedCategory: cat.technicalName
            });
        });

        return sections;
    }

    function handleServiceAccept(cookie, config) {
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
        if (config.google_tag_manager_events && window.dataLayer) {
            window.dataLayer.push({
                event: 'cookie_service_accept',
                cookie_service: cookie.technicalName,
                cookie_category: cookie.categoryTechnicalName
            });
        }

        if (config.matomo_tag_manager_events && window._mtm) {
            window._mtm.push({
                event: 'cookieServiceAccept',
                cookieService: cookie.technicalName,
                cookieCategory: cookie.categoryTechnicalName
            });
        }
    }

    function handleServiceReject(cookie, config) {
        // Dispatch custom event
        document.dispatchEvent(new CustomEvent('cookieServiceReject', {
            detail: { service: cookie.technicalName, category: cookie.categoryTechnicalName }
        }));

        // Push tag manager events
        if (config.google_tag_manager_events && window.dataLayer) {
            window.dataLayer.push({
                event: 'cookie_service_reject',
                cookie_service: cookie.technicalName,
                cookie_category: cookie.categoryTechnicalName
            });
        }

        if (config.matomo_tag_manager_events && window._mtm) {
            window._mtm.push({
                event: 'cookieServiceReject',
                cookieService: cookie.technicalName,
                cookieCategory: cookie.categoryTechnicalName
            });
        }
    }

    function pushTagManagerEvent(eventName, config) {
        if (config.google_tag_manager_events && window.dataLayer) {
            window.dataLayer.push({ event: eventName });
        }

        if (config.matomo_tag_manager_events && window._mtm) {
            const matomoEventName = eventName.replace(/_/g, '').replace(/^cookie/, 'cookie');
            window._mtm.push({ event: matomoEventName });
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

    function updateGoogleConsentMode(config) {
        if (!config.google_consent_mode) return;

        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }

        const analyticsAccepted = CookieConsent.acceptedCategory('analytics');
        const marketingAccepted = CookieConsent.acceptedCategory('marketing');
        const comfortAccepted = CookieConsent.acceptedCategory('comfort');

        gtag('consent', 'update', {
            'ad_storage': marketingAccepted ? 'granted' : 'denied',
            'ad_user_data': marketingAccepted ? 'granted' : 'denied',
            'ad_personalization': marketingAccepted ? 'granted' : 'denied',
            'analytics_storage': analyticsAccepted ? 'granted' : 'denied',
            'functionality_storage': comfortAccepted ? 'granted' : 'denied',
            'personalization_storage': comfortAccepted ? 'granted' : 'denied'
        });
    }

    function createPreferencesButton(config) {
        const btn = document.createElement('button');
        btn.className = 'cc-preferences-btn cc-preferences-btn--' + (config.preferences_button_position || 'bottom-left');
        btn.innerHTML = config.preferences_button_icon || '🍪';
        btn.setAttribute('aria-label', 'Cookie-Einstellungen öffnen');
        btn.onclick = () => CookieConsent.showPreferences();
        document.body.appendChild(btn);
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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
    background: #0d6efd;
    color: #fff;
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

.cc-service-info { font-size: 14px; }
.cc-service-desc { margin-bottom: 10px; }
.cc-service-details { margin: 10px 0; padding-left: 20px; }
.cc-service-details li { margin-bottom: 5px; }
.cc-cookie-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
.cc-cookie-table th, .cc-cookie-table td { border: 1px solid #ddd; padding: 6px; text-align: left; }
.cc-cookie-table th { background: #f5f5f5; }
</style>
<!-- /WSC Cookie Consent -->
HTML;
    }
}
