# WSC Sulu Cookie Consent Bundle

Ein Sulu CMS Bundle für DSGVO-konforme Cookie-Verwaltung basierend auf der [Orest Bida Cookie Consent](https://github.com/orestbida/cookieconsent) JavaScript-Bibliothek.

## Features

- Cookie-Kategorien und Cookies im Sulu Admin verwalten
- Individuelle Zustimmung pro Cookie (Service-Level Consent)
- Detaillierte Cookie-Metadaten (Anbieter, erhobene Daten, Rechtsgrundlage, Verarbeitungsort)
- Google Consent Mode v2 Integration
- Google Tag Manager & Matomo Tag Manager Events
- Mehrsprachigkeit (DE/EN)
- Import/Export als JSON
- Konfigurierbare Banner-Optionen (Position, Layout, Theme)
- Twig Extension und Snippet-Integration

## Anforderungen

- PHP 8.1+
- Sulu CMS 2.5+
- Symfony 6.x oder 7.x

## Installation

### 1. Composer

```bash
composer require wsc/sulu-cookie-consent-bundle
```

### 2. Bundle registrieren

Füge das Bundle in `config/bundles.php` hinzu:

```php
return [
    // ...
    WSC\SuluCookieConsentBundle\WSCCookieConsentBundle::class => ['all' => true],
];
```

### 3. Routing konfigurieren

Erstelle `config/routes/wsc_cookie_consent.yaml`:

```yaml
wsc_cookie_consent:
    resource: '@WSCCookieConsentBundle/Resources/config/routes.yaml'
```

### 4. Assets veröffentlichen

```bash
php bin/console assets:install public
```

### 5. Datenbank aktualisieren

```bash
php bin/console doctrine:schema:update --force
```

Oder mit Doctrine Migrations:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### 6. Standard-Cookies importieren (optional)

```bash
php bin/console wsc:cookie-consent:import-defaults
```

Mit `--force` werden existierende Einträge aktualisiert:

```bash
php bin/console wsc:cookie-consent:import-defaults --force
```

## Konfiguration

Erstelle `config/packages/wsc_cookie_consent.yaml`:

```yaml
wsc_cookie_consent:
    enabled: true
    banner_position: 'bottom-center'  # bottom-center, bottom-left, bottom-right, top, middle
    banner_layout: 'box'              # box, cloud, bar
    theme: 'light'                    # light, dark-turquoise, light-funky, elegant-black, custom
    border_radius: 8
    flip_buttons: false
    equal_weight_buttons: true
    disable_page_interaction: false
    cookie_expires_days: 365
    show_preferences_button: true
    preferences_button_position: 'bottom-left'
    preferences_button_icon: '🍪'

    # Custom Theme Colors (nur wenn theme: custom)
    custom_background_color: '#ffffff'
    custom_text_color: '#333333'
    custom_primary_button_bg_color: '#0d6efd'
    custom_primary_button_text_color: '#ffffff'
    custom_secondary_button_bg_color: '#eeeeee'
    custom_secondary_button_text_color: '#333333'

    # Tag Manager Integration
    google_consent_mode: false
    google_tag_manager_events: false
    matomo_tag_manager_events: false
```

## Verwendung

### Twig Extension

Füge den Cookie Consent Banner in dein `base.html.twig` ein:

```twig
<!DOCTYPE html>
<html>
<head>
    {# ... #}
</head>
<body>
    {# ... dein Content ... #}

    {# Cookie Consent Banner #}
    {% if sulu_cookie_consent_enabled() %}
        {{ sulu_cookie_consent()|raw }}
    {% endif %}
</body>
</html>
```

### Als Snippet

Du kannst den Cookie Consent auch als Sulu Snippet verwenden und ihn über das Snippet-System in deine Seiten einbinden.

### JavaScript Events

Das Bundle dispatcht folgende Custom Events:

```javascript
// Service akzeptiert
document.addEventListener('cookieServiceAccept', (e) => {
    console.log('Service accepted:', e.detail.service, e.detail.category);
});

// Service abgelehnt
document.addEventListener('cookieServiceReject', (e) => {
    console.log('Service rejected:', e.detail.service, e.detail.category);
});
```

### Tag Manager Integration

**Google Tag Manager:**
- `cookie_consent_update` - Bei jeder Änderung
- `cookie_consent_given` - Beim ersten Consent
- `cookie_service_accept` - Wenn ein Service akzeptiert wird
- `cookie_service_reject` - Wenn ein Service abgelehnt wird

**Matomo Tag Manager:**
- `cookieConsentUpdate`
- `cookieConsentGiven`
- `cookieServiceAccept`
- `cookieServiceReject`

## Admin-Bereich

Nach der Installation findest du im Sulu Admin einen neuen Menüpunkt "Cookie Consent" mit:

- **Cookie Kategorien**: Verwalte Kategorien wie "Notwendig", "Statistik", "Marketing"
- **Cookies**: Verwalte einzelne Cookies/Services mit allen Details

### Import/Export

Der Export/Import ist über die Admin API verfügbar:

```
GET /admin/api/cookie-consent/export
POST /admin/api/cookie-consent/import
```

## API

### Config Endpoint

```
GET /api/cookie-consent/config
```

Liefert die komplette Konfiguration als JSON:

```json
{
    "config": { ... },
    "categories": [ ... ],
    "cookies": [ ... ],
    "revision": "abc12345"
}
```

## Lizenz

MIT License

## Autor

Christian Säum - [Web-SEO-Consulting](https://www.web-seo-consulting.eu)
