# CLAUDE.md – QuoteAgent for Shopware 6

## Projektübersicht

**QuoteAgent** ist ein Shopware 6 Plugin für KI-gestützte B2B-Angebotserstellung.
Zielgruppe: KMU mit 5–50 MA, 5–50 Angebotsanfragen/Monat.

---

## Kernarchitekturprinzip (NICHT verletzen)

```
Symfony berechnet (deterministisch)    KI formuliert (Text)
───────────────────────────────        ──────────────────────
Staffelpreise aus DAL             →    Professionellen Angebotstext
Kundengruppen-Konditionen              Auf Basis fertiger Zahlen
Mindestmarge-Prüfung                   In gewünschter Tonalität
MwSt-Berechnung                        Auf Deutsch
```

**Die KI rechnet niemals selbst. Sie formuliert nur.**
Alle numerischen Werte werden von PHP berechnet und als fertiges JSON an die KI übergeben.

---

## Technischer Stack

| Schicht        | Technologie                              |
|----------------|------------------------------------------|
| Backend        | PHP 8.2+, Symfony 6.x (via Shopware)    |
| ORM / DB       | Shopware DAL (kein direktes SQL!)        |
| Admin-UI       | Vue.js 3 (Shopware Admin Component Lib) |
| Storefront     | Twig + Vanilla JS                        |
| PDF            | DomPDF ^2.0                              |
| KI-Adapter     | Anthropic Claude API / OpenAI API (BYOK)|
| Tests          | PHPUnit 10+ (Unit + Integration)         |
| Linter         | PHP CS Fixer (PSR-12)                    |

---

## PHP-Konventionen

### Namespaces & Verzeichnisstruktur

```
QuoteAgent\Core\Quote\          → Entity, Definition, Repository
QuoteAgent\Core\Pricing\        → QuotePricingService, Value Objects
QuoteAgent\Core\AI\             → QuoteTextGeneratorService, Adapter
QuoteAgent\Core\Pdf\            → QuotePdfService
QuoteAgent\Controller\          → QuoteRequestController (Storefront + Admin)
QuoteAgent\Subscriber\          → Event Subscriber
QuoteAgent\ScheduledTask\       → Cron Jobs
```

### PHP Standards

- **PSR-12** Coding Style (erzwungen per PHP CS Fixer)
- **PHP 8.2+** Features nutzen: `readonly`, Named Arguments, Enums
- **Type Declarations**: Alle Parameter und Rückgabewerte typisiert – keine `mixed` ohne Kommentar
- **Value Objects** für berechnete Ergebnisse: immutable, `readonly` Properties
- **Final Classes** als Standard, nur aufbrechen wenn explizit nötig
- Kein direktes SQL – ausschließlich Shopware DAL (`EntityRepository`, `Criteria`)
- Keine statischen Methoden außer `getTaskName()`, `getDefaultInterval()` bei ScheduledTasks

### Fehlerbehandlung

- Alle externen Calls (KI-API, SMTP) in try/catch kapseln
- **Graceful Degradation**: API-Fehler → Platzhalter-Text, kein Datenverlust
- Exceptions loggen (Shopware `LoggerInterface`), nie API-Keys loggen
- HTTP-Fehler-Mapping: 401 → Admin-Benachrichtigung, 429 → 1x Retry (60s), 5xx/Timeout → Fallback

### Sicherheit

- API-Keys nur in Shopware `SystemConfigService` speichern, masked anzeigen (letzte 4 Zeichen)
- `accept_token`: `bin2hex(random_bytes(32))` – kryptographisch sicher, 64 Zeichen
- DomPDF: `isRemoteEnabled = false` (kein externes HTTP aus PDF-Rendering)
- PDF nur über authentifizierten Admin-Endpoint auslieferbar (nicht öffentlich)
- CSRF-Schutz auf allen Storefront-Formularen (Shopware-Standard)

---

## Shopware-spezifische Regeln

### DAL (Data Abstraction Layer)

```php
// RICHTIG: DAL verwenden
$criteria = new Criteria([$productId]);
$criteria->addAssociation('prices');
$this->productRepository->search($criteria, $context)->first();

// FALSCH: Direktes SQL
$this->connection->fetchOne('SELECT * FROM product WHERE id = ?', [$id]);
```

- Shopware `SalesChannelProductPriceCalculator` für Preisberechnung nutzen (nicht selbst rechnen)
- `SalesChannelContext` immer durchreichen – nie selbst Context aufbauen
- Entities immer über Repository schreiben, nie direkt per DB-Connection

### Services

- Alle Services in `Resources/config/services.xml` registrieren
- Services sind **zustandslos**: kein State zwischen Aufrufen, kein Caching auf Instanzebene
- Dependency Injection über Konstruktor (kein Service-Locator-Pattern)
- Interfaces für alle externen Adapter (AI-Provider, Mail) – ermöglicht Mocking in Tests

### Events

- Eigene Events für wichtige Domain-Aktionen (`QuoteRequestCreatedEvent`, etc.)
- Subscriber registrieren in `services.xml` mit `kernel.event_subscriber`-Tag

### Migrations

- Eine Migration pro Schema-Änderung
- Immer `up()` UND `down()` implementieren
- Keine Daten in Migrations – nur Struktur

---

## Test-Strategie

### Pflichtregeln

- **Kein Feature ohne Tests** – PRs ohne Tests werden nicht gemerged
- **Kein Merge bei failing Tests** – CI muss grün sein
- Unit-Tests müssen ohne laufenden Shopware-Shop funktionieren (Mocking via PHPUnit Mocks)

### Test-Typen

| Typ               | Tool      | Ort                        | Wann                              |
|-------------------|-----------|----------------------------|-----------------------------------|
| Unit-Test         | PHPUnit   | `tests/Unit/`              | Jede Klasse mit Logik             |
| Integration-Test  | PHPUnit   | `tests/Integration/`       | DAL, Controller, Events           |
| Akzeptanztest     | Manuell / Playwright | Separate Checkliste | Vor jedem Release               |

### Was MUSS getestet werden

**QuotePricingService:**
- Staffelpreisauflösung für verschiedene Mengen
- Kundengruppen-Netto/Brutto-Kalkulation
- Margenberechnung (positiv, null, unter Mindestmarge)
- Edge Case: kein Einkaufspreis vorhanden → Marge = null, keine Warnung

**QuoteTextGeneratorService:**
- Prompt wird korrekt zusammengestellt (ohne echten API-Call!)
- Fallback-Text bei jedem Fehlertyp (401, 429, 5xx, Timeout)
- API-Key wird nicht in generierten Strings/Logs weitergegeben

**QuotePdfService:**
- HTML-Output enthält alle Pflichtfelder (Angebotsnr., Positionen, Summen)
- Zahlenformatierung auf Deutsch (Komma als Dezimaltrenner, Punkt als Tausender)
- Graceful Degradation wenn kein Logo vorhanden

**QuoteRequestController:**
- Validierung: ungültige Mengen werden abgefangen
- CSRF-Prüfung aktiv
- B2C-Kunden sehen kein Formular (Kundengruppen-Check)
- Token-Lookup: ungültiger Token → informative Fehlerseite, kein 500

**Status-Maschine:**
- Nur erlaubte Übergänge (new→sent, sent→accepted, etc.)
- Idempotenz: Doppelklick "Annehmen" ändert Status nicht ein zweites Mal

### Test-Patterns

```php
// RICHTIG: Abhängigkeiten mocken
$repository = $this->createMock(EntityRepository::class);
$service = new QuotePricingService($repository, $systemConfig, $logger);

// FALSCH: Echter DB-Call im Unit-Test
$kernel = KernelLifecycleManager::bootKernel();
```

```php
// Value Objects testen: Immutabilität prüfen
$result = new QuoteCalculationResult(...);
$this->assertSame(1290.00, $result->totalNet);
$this->assertTrue($result->belowMinMargin);
```

---

## DRY / KISS / SOLID

### DRY (Don't Repeat Yourself)

- Preisformatierung zentral in einer Helper-Klasse (`GermanNumberFormatter`)
- Mail-Versand-Logik nur in einem Service, nicht im Controller
- Tonalitäts-Mapping als Konstante/Enum, nicht als String-Literal an mehreren Stellen

### KISS (Keep It Simple, Stupid)

- Keine vorzeitige Abstraktion – erst wenn eine zweite Verwendung absehbar ist
- Factory-Pattern für AI-Provider-Selektion: max. 30 Zeilen
- Status-Übergänge als einfache `match`-Expression, kein State-Pattern (overkill für 5 Zustände)

### SOLID

- **S**: Jeder Service hat eine Verantwortung (Preis ODER Text ODER PDF, nie alles)
- **O**: AI-Provider über Interface erweiterbar ohne bestehenden Code zu ändern
- **L**: `AiProviderInterface` mit klarer Signatur – alle Implementierungen austauschbar
- **I**: Schmale Interfaces (`AiProviderInterface` nur 2 Methoden)
- **D**: Services bekommen Abhängigkeiten injiziert, konstruieren sie nicht selbst

---

## Git-Workflow

### Branch-Naming

```
feature/001-plugin-skeleton
feature/003-pricing-service
fix/pdf-umlaut-encoding
```

### Commit-Messages (Imperativ, auf Englisch)

```
Add QuoteRequestEntity with DAL definition
Implement tier price resolution in QuotePricingService
Fix umlaut encoding in DomPDF template
Add unit tests for margin calculation edge cases
```

### PR-Checkliste (vor Merge)

- [ ] Alle Tests grün (`composer test`)
- [ ] PHP CS Fixer ohne Fehler (`composer cs-fix --dry-run`)
- [ ] Keine TODOs im Produktionscode
- [ ] Akzeptanzkriterien aus dem PRD abgehakt
- [ ] Code Review durch Claude durchgeführt

---

## Verbote (NIEMALS tun)

- `❌` Direktes SQL (nur DAL)
- `❌` API-Key in Logs schreiben
- `❌` KI lässt rechnen – KI bekommt nur fertige Zahlen
- `❌` PDF über öffentliche URL ausliefern
- `❌` Service mit globalem State (kein Singleton-Muster)
- `❌` Tests modifizieren um failing Tests zu kaschieren
- `❌` Features aus "Out of Scope" (V1.0 bleibt V1.0)
- `❌` Eigene Kryptographie – `random_bytes()` für Tokens
- `❌` Shopware Core-Klassen überschreiben (nur Dekoratoren wenn unvermeidbar)

---

## Abhängigkeiten (composer.json)

```json
{
  "require": {
    "php": ">=8.2",
    "shopware/core": "~6.6.0",
    "dompdf/dompdf": "^2.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^10.0",
    "friendsofphp/php-cs-fixer": "^3.0"
  }
}
```

---

## Lokale Entwicklungsumgebung

```bash
# Tests ausführen
composer test

# Code-Style prüfen
composer cs-fix --dry-run

# Code-Style auto-fixen
composer cs-fix

# Plugin installieren (im Shopware-Root)
bin/console plugin:install --activate QuoteAgent
```

---

## Offene V1.0-Entscheidungen (festgelegt)

| Frage | Entscheidung |
|-------|-------------|
| Mindestmarge: hard limit oder Warnung? | **Nur Warnung** – Händler entscheidet |
| Quote-Nummer-Generierung | PHP-Service mit `YYYYMMDD-XXXX`-Format |
| Accept-Token nach Verwendung invalidieren? | **Nein** – Link bleibt abrufbar, zeigt Status |
| Produkt ohne Einkaufspreis? | Marge = null, keine Warnung anzeigen |
