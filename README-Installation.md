# WSC Sulu Cookie Consent Bundle - Installationsanleitung

Diese Anleitung beschreibt die Installation des Cookie Consent Bundles in einem **Sulu CMS 3.0** Projekt.

## Inhaltsverzeichnis

1. [Voraussetzungen](#voraussetzungen)
2. [Installation](#installation)
   - [Option A: Lokales Path-Repository](#option-a-lokales-path-repository-entwicklung)
   - [Option B: Git Repository (GitHub)](#option-b-git-repository-github)
3. [Konfiguration](#konfiguration)
4. [Datenbank einrichten](#datenbank-einrichten)
5. [Assets veröffentlichen](#assets-veröffentlichen)
6. [Standard-Cookies importieren](#standard-cookies-importieren)
7. [In Templates einbinden](#in-templates-einbinden)
8. [Fehlerbehebung](#fehlerbehebung)

---

## Voraussetzungen

- **PHP 8.2** oder höher
- **Sulu CMS 3.0** (basierend auf Symfony 7.4)
- **Composer 2.x**
- **MySQL/MariaDB** oder PostgreSQL

---

## Installation

### Option A: Lokales Path-Repository (Entwicklung)

Ideal wenn das Bundle-Verzeichnis auf dem gleichen Server/Rechner liegt.

**Schritt 1:** Bundle-Verzeichnis bereitstellen

Das Bundle sollte außerhalb deines Sulu-Projekts liegen, z.B.:
```
/var/www/bundles/WSCBundleSuluCookieOrestBida/
```

Oder auf deinem Entwicklungsrechner:
```
/home/user/Projects/WSCBundleSuluCookieOrestBida/
```

**Schritt 2:** Repository in Sulu's `composer.json` hinzufügen

Öffne die `composer.json` deines Sulu-Projekts und füge hinzu:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/var/www/bundles/WSCBundleSuluCookieOrestBida",
            "options": {
                "symlink": true
            }
        }
    ]
}
```

**Schritt 3:** Bundle installieren

```bash
cd /var/www/html
composer require wsc/sulu-cookie-consent-bundle:@dev
```

---

### Option B: Git Repository (GitHub)

**Schritt 1:** Bundle auf GitHub verfügbar machen

Falls noch nicht geschehen, pushe das Bundle zu GitHub:
```bash
cd /path/to/WSCBundleSuluCookieOrestBida
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/csaeum/WSCBundleSuluCookieOrestBida.git
git push -u origin main
```

**Schritt 2:** Repository in Sulu's `composer.json` hinzufügen

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/csaeum/WSCBundleSuluCookieOrestBida.git"
        }
    ]
}
```

**Schritt 3:** Bundle installieren

```bash
cd /var/www/html
composer require wsc/sulu-cookie-consent-bundle:dev-main
```

---

## Konfiguration

### 1. Bundle registrieren

Das Bundle sollte automatisch registriert werden. Falls nicht, füge es manuell in `config/bundles.php` hinzu:

```php
<?php

return [
    // ... andere Bundles ...
    WSC\SuluCookieConsentBundle\WSCCookieConsentBundle::class => ['all' => true],
];
```

### 2. Routing konfigurieren

Erstelle `config/routes/wsc_cookie_consent.yaml`:

```yaml
wsc_cookie_consent:
    resource: '@WSCCookieConsentBundle/Resources/config/routes.yaml'
```

### 3. Bundle-Konfiguration erstellen

Erstelle `config/packages/wsc_cookie_consent.yaml`:

```yaml
wsc_cookie_consent:
    # Aktivierung
    enabled: true

    # Banner-Erscheinung
    banner_position: 'bottom-center'  # bottom-center, bottom-left, bottom-right, top, middle
    banner_layout: 'box'              # box, cloud, bar
    theme: 'light'                    # light, dark-turquoise, light-funky, elegant-black, custom
    border_radius: 8

    # Button-Verhalten
    flip_buttons: false
    equal_weight_buttons: true
    disable_page_interaction: false

    # Cookie-Einstellungen
    cookie_expires_days: 365

    # Preferences-Button (Floating Button zum Öffnen der Einstellungen)
    show_preferences_button: true
    preferences_button_position: 'bottom-left'
    preferences_button_icon: '🍪'

    # Tag Manager Integration
    google_consent_mode: false
    google_tag_manager_events: false
    matomo_tag_manager_events: false
```

---

## Datenbank einrichten

### Mit Doctrine Schema Update (Entwicklung)

```bash
php bin/console doctrine:schema:update --force
```

### Mit Doctrine Migrations (Produktion)

```bash
# Migration erstellen
php bin/console doctrine:migrations:diff

# Prüfen
php bin/console doctrine:migrations:status

# Ausführen
php bin/console doctrine:migrations:migrate
```

**Erstellte Tabellen:**
- `wsc_cookie_category`
- `wsc_cookie_category_translation`
- `wsc_cookie`
- `wsc_cookie_translation`
- `wsc_cookie_item`
- `wsc_cookie_item_translation`

---

## Assets veröffentlichen

```bash
php bin/console assets:install public
```

Die Cookie Consent JavaScript/CSS-Dateien werden nach `public/bundles/wsccookieconsent/` kopiert.

---

## Standard-Cookies importieren

Importiere die vordefinierten Cookie-Kategorien und Cookies:

```bash
php bin/console wsc:cookie-consent:import-defaults
```

Mit `--force` werden existierende Einträge aktualisiert:

```bash
php bin/console wsc:cookie-consent:import-defaults --force
```

**Importierte Kategorien:**
- Notwendig (Pflicht, nicht deaktivierbar)
- Komfort
- Statistik
- Marketing
- Social Media

---

## In Templates einbinden

Öffne dein Base-Template (z.B. `templates/base.html.twig`) und füge vor `</body>` ein:

```twig
{# Cookie Consent Banner #}
{% if sulu_cookie_consent_enabled() %}
    {{ sulu_cookie_consent()|raw }}
{% endif %}
```

**Beispiel für ein vollständiges Base-Template:**

```twig
<!DOCTYPE html>
<html lang="{{ request.locale }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}{{ content.title }}{% endblock %}</title>
    {% block head %}{% endblock %}
</head>
<body>
    {% block header %}{% endblock %}

    {% block content %}{% endblock %}

    {% block footer %}{% endblock %}

    {# Cookie Consent - muss vor </body> stehen #}
    {% if sulu_cookie_consent_enabled() %}
        {{ sulu_cookie_consent()|raw }}
    {% endif %}
</body>
</html>
```

---

## Cache leeren

```bash
php bin/console cache:clear
```

---

## Überprüfung

### 1. Admin-Bereich

Öffne den Sulu Admin (`/admin`) und prüfe:
- Neuer Menüpunkt "Cookie Consent" in der Navigation
- Kategorien und Cookies sind sichtbar

### 2. API testen

Rufe auf: `https://deine-domain.de/api/cookie-consent/config`

Du solltest eine JSON-Antwort mit der Konfiguration erhalten.

### 3. Frontend testen

Öffne deine Website im **Inkognito-Modus** (um vorhandene Cookies zu umgehen):
- Cookie-Banner sollte erscheinen
- Buttons funktionieren
- Einwilligung wird gespeichert

---

## Fehlerbehebung

### "Bundle not found"

```bash
composer dump-autoload
php bin/console cache:clear
```

### "Route not found"

Prüfe ob `config/routes/wsc_cookie_consent.yaml` existiert:
```bash
php bin/console debug:router | grep cookie
```

### "Table not found"

```bash
php bin/console doctrine:schema:update --force
```

### Admin-Menü erscheint nicht

1. Prüfe Berechtigungen unter "Einstellungen" → "Rollen"
2. Füge "Cookie Consent" Berechtigung hinzu
3. Ausloggen und neu einloggen

### Assets werden nicht geladen (404)

```bash
php bin/console assets:install public --symlink
```

---

## Komplette Installations-Checkliste

```bash
# 1. Bundle installieren
composer require wsc/sulu-cookie-consent-bundle:@dev

# 2. Routing erstellen
cat > config/routes/wsc_cookie_consent.yaml << 'EOF'
wsc_cookie_consent:
    resource: '@WSCCookieConsentBundle/Resources/config/routes.yaml'
EOF

# 3. Konfiguration erstellen
cat > config/packages/wsc_cookie_consent.yaml << 'EOF'
wsc_cookie_consent:
    enabled: true
    banner_position: 'bottom-center'
    banner_layout: 'box'
    theme: 'light'
    show_preferences_button: true
    preferences_button_position: 'bottom-left'
EOF

# 4. Datenbank aktualisieren
php bin/console doctrine:schema:update --force

# 5. Assets installieren
php bin/console assets:install public

# 6. Standard-Daten importieren
php bin/console wsc:cookie-consent:import-defaults

# 7. Cache leeren
php bin/console cache:clear

# 8. Template anpassen (manuell)
# Füge {{ sulu_cookie_consent()|raw }} vor </body> ein
```

---

## Support

- GitHub Issues: https://github.com/csaeum/WSCBundleSuluCookieOrestBida/issues
- Website: https://www.web-seo-consulting.eu
