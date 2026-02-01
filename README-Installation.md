# WSC Sulu Cookie Consent Bundle - Installationsanleitung

Diese Anleitung beschreibt die Installation des Cookie Consent Bundles in einem Sulu CMS Projekt.

## Inhaltsverzeichnis

1. [Voraussetzungen](#voraussetzungen)
2. [Installation](#installation)
   - [Option A: Lokales Path-Repository (Entwicklung)](#option-a-lokales-path-repository-entwicklung)
   - [Option B: Git Repository (GitHub)](#option-b-git-repository-github)
   - [Option C: Packagist (nach Veröffentlichung)](#option-c-packagist-nach-veröffentlichung)
3. [Bundle registrieren](#bundle-registrieren)
4. [Routing konfigurieren](#routing-konfigurieren)
5. [Konfiguration erstellen](#konfiguration-erstellen)
6. [Datenbank aktualisieren](#datenbank-aktualisieren)
7. [Assets veröffentlichen](#assets-veröffentlichen)
8. [Standard-Cookies importieren](#standard-cookies-importieren)
9. [In Templates einbinden](#in-templates-einbinden)
10. [Cache leeren](#cache-leeren)
11. [Fehlerbehebung](#fehlerbehebung)

---

## Voraussetzungen

- PHP 8.1 oder höher
- Sulu CMS 2.5 oder höher
- Symfony 6.x oder 7.x
- Composer

---

## Installation

### Option A: Lokales Path-Repository (Entwicklung)

Diese Option ist ideal für die lokale Entwicklung, wenn das Bundle-Verzeichnis auf dem gleichen Rechner liegt.

**1. Bundle-Verzeichnis kopieren oder verlinken**

Kopiere das Bundle-Verzeichnis an einen beliebigen Ort, z.B.:
```
/home/csaeum/PhpstormProjects/GitHub/WSCBundleSuluCookieOrestBida
```

**2. In der `composer.json` deines Sulu-Projekts das Repository hinzufügen:**

Öffne die `composer.json` deines Sulu-Projekts und füge im `repositories`-Bereich hinzu:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/home/csaeum/PhpstormProjects/GitHub/WSCBundleSuluCookieOrestBida",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "...": "...",
        "wsc/sulu-cookie-consent-bundle": "*"
    }
}
```

> **Hinweis:** Passe den Pfad an deine lokale Struktur an. Mit `"symlink": true` wird ein Symlink erstellt, sodass Änderungen am Bundle sofort wirksam werden.

**3. Composer Update ausführen:**

```bash
cd /pfad/zu/deinem/sulu-projekt
composer update wsc/sulu-cookie-consent-bundle
```

---

### Option B: Git Repository (GitHub)

Diese Option ist ideal, wenn das Bundle auf GitHub (oder einem anderen Git-Server) liegt.

**1. Bundle auf GitHub pushen:**

```bash
cd /home/csaeum/PhpstormProjects/GitHub/WSCBundleSuluCookieOrestBida
git add .
git commit -m "Initial commit: WSC Sulu Cookie Consent Bundle"
git push origin main
```

**2. In der `composer.json` deines Sulu-Projekts das Repository hinzufügen:**

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/csaeum/WSCBundleSuluCookieOrestBida"
        }
    ],
    "require": {
        "...": "...",
        "wsc/sulu-cookie-consent-bundle": "dev-main"
    }
}
```

> **Hinweis:** `dev-main` referenziert den `main`-Branch. Für stabile Versionen nutze Git-Tags (z.B. `"^1.0"`).

**3. Composer Update ausführen:**

```bash
composer update wsc/sulu-cookie-consent-bundle
```

---

### Option C: Packagist (nach Veröffentlichung)

Nach Veröffentlichung auf [Packagist](https://packagist.org/):

```bash
composer require wsc/sulu-cookie-consent-bundle
```

---

## Bundle registrieren

Füge das Bundle in `config/bundles.php` deines Sulu-Projekts hinzu:

```php
<?php

return [
    // ... andere Bundles ...

    WSC\SuluCookieConsentBundle\WSCCookieConsentBundle::class => ['all' => true],
];
```

---

## Routing konfigurieren

Erstelle die Datei `config/routes/wsc_cookie_consent.yaml`:

```yaml
wsc_cookie_consent:
    resource: '@WSCCookieConsentBundle/Resources/config/routes.yaml'
```

---

## Konfiguration erstellen

Erstelle die Datei `config/packages/wsc_cookie_consent.yaml`:

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

    # Preferences-Button
    show_preferences_button: true
    preferences_button_position: 'bottom-left'  # bottom-left, bottom-right, top-left, top-right
    preferences_button_icon: '🍪'

    # Custom Theme (nur wenn theme: custom)
    # custom_background_color: '#ffffff'
    # custom_text_color: '#333333'
    # custom_primary_button_bg_color: '#0d6efd'
    # custom_primary_button_text_color: '#ffffff'
    # custom_secondary_button_bg_color: '#eeeeee'
    # custom_secondary_button_text_color: '#333333'

    # Tag Manager Integration
    google_consent_mode: false        # Google Consent Mode v2 aktivieren
    google_tag_manager_events: false  # Events an GTM dataLayer senden
    matomo_tag_manager_events: false  # Events an Matomo Tag Manager senden
```

---

## Datenbank aktualisieren

### Option 1: Schema Update (Entwicklung)

```bash
php bin/console doctrine:schema:update --force
```

### Option 2: Doctrine Migrations (Produktion empfohlen)

```bash
# Migration erstellen
php bin/console doctrine:migrations:diff

# Migration prüfen
php bin/console doctrine:migrations:status

# Migration ausführen
php bin/console doctrine:migrations:migrate
```

**Erstellte Tabellen:**
- `wsc_cookie_category` - Cookie-Kategorien
- `wsc_cookie_category_translation` - Übersetzungen der Kategorien
- `wsc_cookie` - Cookies/Services
- `wsc_cookie_translation` - Übersetzungen der Cookies
- `wsc_cookie_item` - Einzelne Cookie-Einträge
- `wsc_cookie_item_translation` - Übersetzungen der Cookie-Einträge

---

## Assets veröffentlichen

Veröffentliche die JavaScript- und CSS-Dateien:

```bash
php bin/console assets:install public
```

Die Assets werden nach `public/bundles/wsccookieconsent/` kopiert:
- `js/cookieconsent.umd.js`
- `css/cookieconsent.css`

---

## Standard-Cookies importieren

Importiere die vordefinierten Cookie-Kategorien und Cookies:

```bash
# Standard-Import (überspringt existierende Einträge)
php bin/console wsc:cookie-consent:import-defaults

# Mit Überschreiben existierender Einträge
php bin/console wsc:cookie-consent:import-defaults --force

# Import aus eigener JSON-Datei
php bin/console wsc:cookie-consent:import-defaults --file=/pfad/zu/custom-cookies.json
```

**Importierte Kategorien:**
1. Notwendig (Pflicht, vorausgewählt)
2. Komfort
3. Statistik
4. Marketing
5. Social Media

**Importierte Cookies:**
- Session-Cookie (Notwendig)
- Cookie-Einwilligung (Notwendig)
- Google Analytics 4 (Statistik)
- Matomo (Statistik, deaktiviert)
- YouTube (Social Media)
- Google Ads (Marketing, deaktiviert)
- Meta Pixel (Marketing, deaktiviert)

---

## In Templates einbinden

### Option 1: Direkt im Base-Template

Öffne dein `templates/base.html.twig` und füge vor `</body>` ein:

```twig
<!DOCTYPE html>
<html lang="{{ request.locale }}">
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

### Option 2: Als Include

Erstelle `templates/includes/cookie-consent.html.twig`:

```twig
{% if sulu_cookie_consent_enabled() %}
    {{ sulu_cookie_consent()|raw }}
{% endif %}
```

Und binde es ein:

```twig
{% include 'includes/cookie-consent.html.twig' %}
```

### Option 3: Als Sulu Snippet

1. Erstelle im Sulu Admin unter "Snippets" ein neues Snippet vom Typ "Cookie Consent Banner"
2. Binde das Snippet in deinen Seiten oder Templates ein

---

## Cache leeren

Nach der Installation den Cache leeren:

```bash
# Entwicklung
php bin/console cache:clear

# Produktion
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

---

## Überprüfung der Installation

### 1. Admin-Bereich prüfen

Öffne den Sulu Admin und prüfe:
- Neuer Menüpunkt "Cookie Consent" vorhanden?
- Unterpunkte "Cookie Kategorien" und "Cookies" sichtbar?
- Kategorien und Cookies wurden importiert?

### 2. Frontend-API prüfen

Rufe im Browser auf:
```
https://deine-domain.de/api/cookie-consent/config
```

Du solltest eine JSON-Antwort mit Konfiguration, Kategorien und Cookies erhalten.

### 3. Banner prüfen

Öffne deine Website im Inkognito-Modus (um vorhandene Consent-Cookies zu umgehen) und prüfe:
- Wird der Cookie-Banner angezeigt?
- Funktionieren die Buttons?
- Wird die Einwilligung gespeichert?

---

## Fehlerbehebung

### Problem: Bundle wird nicht gefunden

```
[Symfony\Component\Config\Exception\FileLoaderLoadException]
Bundle "WSCCookieConsentBundle" does not exist
```

**Lösung:**
1. Prüfe, ob das Bundle in `config/bundles.php` registriert ist
2. Führe `composer dump-autoload` aus
3. Leere den Cache: `php bin/console cache:clear`

### Problem: Routen nicht gefunden

```
No route found for "GET /api/cookie-consent/config"
```

**Lösung:**
1. Prüfe, ob `config/routes/wsc_cookie_consent.yaml` existiert
2. Leere den Cache
3. Prüfe mit: `php bin/console debug:router | grep cookie`

### Problem: Tabellen existieren nicht

```
SQLSTATE[42S02]: Base table or view not found
```

**Lösung:**
```bash
php bin/console doctrine:schema:update --force
```

### Problem: Assets werden nicht geladen

```
404 Not Found: /bundles/wsccookieconsent/js/cookieconsent.umd.js
```

**Lösung:**
```bash
php bin/console assets:install public --symlink
```

### Problem: Admin-Menü erscheint nicht

**Lösung:**
1. Prüfe die Berechtigungen im Sulu Admin unter "Einstellungen" → "Rollen"
2. Füge die Berechtigung "Cookie Consent" für die entsprechende Rolle hinzu
3. Logge dich aus und wieder ein

### Problem: Übersetzungen fehlen

**Lösung:**
```bash
php bin/console cache:clear
php bin/console translation:update --force de
php bin/console translation:update --force en
```

---

## Deinstallation

Falls du das Bundle entfernen möchtest:

```bash
# 1. Bundle aus composer.json entfernen
composer remove wsc/sulu-cookie-consent-bundle

# 2. Bundle aus config/bundles.php entfernen

# 3. Routing-Datei löschen
rm config/routes/wsc_cookie_consent.yaml

# 4. Konfiguration löschen
rm config/packages/wsc_cookie_consent.yaml

# 5. Tabellen entfernen (ACHTUNG: Datenverlust!)
php bin/console doctrine:schema:update --force
# Oder manuell die Tabellen löschen:
# wsc_cookie_item_translation
# wsc_cookie_item
# wsc_cookie_translation
# wsc_cookie
# wsc_cookie_category_translation
# wsc_cookie_category

# 6. Assets entfernen
rm -rf public/bundles/wsccookieconsent

# 7. Cache leeren
php bin/console cache:clear
```

---

## Support

Bei Fragen oder Problemen:
- GitHub Issues: https://github.com/csaeum/WSCBundleSuluCookieOrestBida/issues
- Website: https://www.web-seo-consulting.eu
