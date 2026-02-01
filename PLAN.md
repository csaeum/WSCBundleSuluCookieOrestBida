# Sulu Cookie Consent Bundle - Implementierungsplan

## Projektübersicht
Ein Sulu CMS Bundle für DSGVO-konforme Cookie-Verwaltung basierend auf der Orest Bida Cookie Consent JavaScript-Bibliothek.

## Konfiguration
- **Bundle-Typ:** Composer-Paket (eigenständiges Repository)
- **Admin-UI:** Sulu Admin (React)
- **JS-Library:** Lokal kopiert (im Bundle-Assets-Ordner)
- **Integration:** Beides (Snippet + Twig-Extension)

## Referenz
- Shopware Plugin: https://github.com/csaeum/WSCPluginSWCookieOrestBida

---

## Fortschritt

### Phase 1: Grundstruktur ✅ ABGESCHLOSSEN
- [x] composer.json erstellen
- [x] Bundle-Klasse erstellen
- [x] DependencyInjection Extension
- [x] Services.yaml Konfiguration
- [x] Bundle-Konfiguration (sulu_cookie_consent.yaml Schema)
- [x] Routes.yaml Konfiguration

### Phase 2: Entities ✅ ABGESCHLOSSEN
- [x] CookieCategory Entity + Translation
- [x] Cookie Entity + Translation
- [x] CookieItem Entity + Translation
- [x] Repository-Klassen (CookieCategoryRepository, CookieRepository, CookieItemRepository)

### Phase 3: Sulu Admin Integration ✅ ABGESCHLOSSEN
- [x] Admin-Klasse für Navigation (CookieConsentAdmin.php)
- [x] CookieCategoryController + ListMetadata + FormMetadata
- [x] CookieController + ListMetadata + FormMetadata
- [x] Import/Export Controller
- [x] Admin Translations (DE/EN)

### Phase 4: API & Frontend ✅ ABGESCHLOSSEN
- [x] Cookie Consent Config API Endpoint (CookieConsentController.php)
- [x] Twig Extension (sulu_cookie_consent)
- [x] JavaScript-Integration (OrestBida Cookie Consent v3)
- [x] CSS/JS Assets heruntergeladen und lokal gespeichert
- [x] CookieConsentConfigProvider Service

### Phase 5: Snippet Integration ✅ ABGESCHLOSSEN
- [x] Snippet Template erstellen (cookie-consent.html.twig)
- [x] Snippet XML Definition

### Phase 6: Standard-Daten & Übersetzungen ✅ ABGESCHLOSSEN
- [x] default-cookies.json erstellen (5 Kategorien, 7 Cookies)
- [x] ImportDefaultCookiesCommand Console Command
- [x] Admin Translations (DE/EN)

### Phase 7: Finalisierung ✅ ABGESCHLOSSEN
- [x] README.md Dokumentation
- [x] Installations-Anleitung
- [ ] Tests (Optional - nicht implementiert)

---

## Aktuelle Session

### 2026-02-01 - Session 1
**Status:** ✅ ABGESCHLOSSEN

**Implementiert:**
1. Grundstruktur mit composer.json, Bundle-Klasse, DI Extension
2. Alle Entity-Klassen mit Übersetzungen
3. Repositories für alle Entities
4. Sulu Admin Integration mit Navigation und CRUD
5. API Endpoint für Frontend-Konfiguration
6. Twig Extension mit vollständiger JavaScript-Integration
7. OrestBida Cookie Consent JS/CSS lokal eingebunden
8. Snippet-Templates für Sulu
9. Standard-Cookie-Daten als JSON
10. Console Command für Datenimport
11. README.md mit vollständiger Dokumentation

### 2026-02-01 - Session 1 (Update)
**Status:** ✅ Anpassungen für Sulu 3.0

**Änderungen:**
1. composer.json für Sulu 3.0 / Symfony 7.4 angepasst
   - Type: `symfony-bundle` statt `sulu-bundle`
   - PHP ^8.2
   - Symfony ^7.0
   - sulu/sulu als `require-dev` statt `require`
2. README-Installation.md für Sulu 3.0 aktualisiert
3. Konflikt mit bestehenden Sulu-Paketen behoben

---

## Erstellte Dateien

```
WSCBundleSuluCookieOrestBida/
├── composer.json
├── README.md
├── PLAN.md
├── src/
│   ├── WSCCookieConsentBundle.php
│   ├── Admin/
│   │   └── CookieConsentAdmin.php
│   ├── Command/
│   │   └── ImportDefaultCookiesCommand.php
│   ├── Controller/
│   │   ├── Admin/
│   │   │   ├── CookieCategoryController.php
│   │   │   ├── CookieController.php
│   │   │   └── ImportExportController.php
│   │   └── Website/
│   │       └── CookieConsentController.php
│   ├── DependencyInjection/
│   │   ├── Configuration.php
│   │   └── WSCCookieConsentExtension.php
│   ├── Entity/
│   │   ├── CookieCategory.php
│   │   ├── CookieCategoryTranslation.php
│   │   ├── Cookie.php
│   │   ├── CookieTranslation.php
│   │   ├── CookieItem.php
│   │   └── CookieItemTranslation.php
│   ├── Repository/
│   │   ├── CookieCategoryRepository.php
│   │   ├── CookieRepository.php
│   │   └── CookieItemRepository.php
│   ├── Service/
│   │   └── CookieConsentConfigProvider.php
│   ├── Twig/
│   │   └── CookieConsentExtension.php
│   └── Resources/
│       ├── config/
│       │   ├── services.yaml
│       │   ├── routes.yaml
│       │   ├── lists/
│       │   │   ├── cookie_categories.xml
│       │   │   └── cookies.xml
│       │   ├── forms/
│       │   │   ├── cookie_category_details.xml
│       │   │   └── cookie_details.xml
│       │   └── snippet/
│       │       └── cookie-consent.xml
│       ├── data/
│       │   └── default-cookies.json
│       ├── public/
│       │   ├── js/
│       │   │   └── cookieconsent.umd.js
│       │   └── css/
│       │       └── cookieconsent.css
│       ├── templates/
│       │   ├── snippets/
│       │   │   └── cookie-consent.html.twig
│       │   └── cookie_consent.html.twig
│       └── translations/
│           ├── admin.de.yaml
│           └── admin.en.yaml
```

---

## Nächste Schritte (Optional)

Falls du das Bundle erweitern möchtest:

1. **Unit Tests** - PHPUnit Tests für Repositories und Services
2. **Functional Tests** - Controller-Tests
3. **Cookie Items UI** - Inline-Bearbeitung im Cookie-Formular
4. **Doctrine Migrations** - Automatische Migrations erstellen
5. **CI/CD** - GitHub Actions für Tests und Code-Qualität

---

## Notizen

### Sulu-spezifische Unterschiede zu Shopware
1. **Entities:** Sulu nutzt Doctrine ORM statt DAL
2. **Übersetzungen:** Sulu hat eigenes Localization-System (Translation Entities)
3. **Admin:** React-basiert mit eigener Formular-Struktur (XML Metadaten)
4. **Snippets:** Sulu Snippet System für wiederverwendbare Content-Blöcke

### OrestBida Cookie Consent v3
- Version: 3.0.1
- Lokal eingebunden in `/public/bundles/wsccookieconsent/`
- Dokumentation: https://cookieconsent.orestbida.com/
