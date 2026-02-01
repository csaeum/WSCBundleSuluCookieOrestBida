# Cookie Consent Snippet einrichten

## Schritt 1: Snippet-Template kopieren

Kopiere das Snippet-Template in dein Projekt:

```bash
mkdir -p config/templates/snippets
cp vendor/wsc/sulu-cookie-consent-bundle/src/Resources/templates/snippets/cookie-consent.xml config/templates/snippets/
```

## Schritt 2: Twig-Template kopieren

Kopiere das Twig-Template:

```bash
mkdir -p templates/snippets
cp vendor/wsc/sulu-cookie-consent-bundle/src/Resources/templates/snippets/cookie-consent.html.twig templates/snippets/
```

## Schritt 3: Cache leeren

```bash
php bin/console cache:clear
```

## Schritt 4: Snippet erstellen

1. Gehe zu Sulu Admin → Snippets
2. Klicke auf "Neu"
3. Wähle Template "Cookie Consent Banner"
4. Konfiguriere die Einstellungen
5. Speichern

## Schritt 5: In Base-Template einbinden

Füge in deinem `templates/base.html.twig` vor `</body>` ein:

```twig
{# Cookie Consent Snippet laden #}
{% set cookieConsentSnippet = sulu_snippet_load_by_area('cookie-consent') %}
{% if cookieConsentSnippet %}
    {{ include(cookieConsentSnippet.view, cookieConsentSnippet) }}
{% endif %}
```

## Alternative: Ohne Snippet

Du kannst den Cookie Consent auch direkt ohne Snippet einbinden:

```twig
{# In templates/base.html.twig vor </body> #}
{{ sulu_cookie_consent()|raw }}
```

Diese Variante nutzt die Bundle-Konfiguration aus `config/packages/wsc_cookie_consent.yaml`.
