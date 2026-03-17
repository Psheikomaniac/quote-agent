# QuoteAgent for Shopware 6

> KI-gestützte B2B-Angebotserstellung für Shopware 6 Rise Plan & Community Edition.

**Zielgruppe:** KMU mit 5–50 Mitarbeitern, 5–50 Angebotsanfragen/Monat
**Budget:** 49–79 €/Monat
**GitHub:** https://github.com/Psheikomaniac/quote-agent

---

## Das Kernprinzip

```
Symfony berechnet (deterministisch)    KI formuliert (Text)
───────────────────────────────        ──────────────────────
Staffelpreise aus DAL             →    Professionellen Angebotstext
Kundengruppen-Konditionen              Auf Basis fertiger Zahlen
Mindestmarge-Prüfung                   In gewünschter Tonalität
MwSt-Berechnung                        Auf Deutsch
```

**Die KI rechnet niemals selbst. Sie formuliert nur.**
Alle numerischen Werte werden von PHP/Symfony berechnet und als fertiges JSON an die KI übergeben. Das garantiert korrekte, prüfbare Zahlen – unabhängig vom verwendeten KI-Modell.

---

## Features (V1.0)

### Storefront
- Angebotsformular eingebettet auf Produktseite, Kategorie-Listing und als Standalone-Route
- Nur sichtbar für konfigurierte B2B-Kundengruppen (nicht für Gäste oder B2C)
- Produktsuche mit Autocomplete (Artikelnummer, Produktname)
- Kundenbereich „Meine Angebote" mit Statusübersicht und Direktlinks

### Automatische Preiskalkulation
- Staffelpreise aus Shopware DAL für die angefragte Menge
- Kundengruppen-spezifische Netto/Brutto-Konditionen
- Margenberechnung pro Position mit konfigurierbarer Mindestmarge (Warnung, kein Blocker)
- Produkt ohne Einkaufspreis → Marge = `null`, keine Warnung

### KI-Textgenerierung (BYOK)
- Händler hinterlegt eigenen API-Key (Anthropic Claude oder OpenAI GPT-4o)
- Konfigurierbarer Prompt: Tonalität (formal, freundlich, technisch) + eigene Anweisungen
- Fallback bei API-Ausfall: Platzhalter-Text, kein Datenverlust
- API-Kosten: ~0,09–0,15 €/Monat bei 30 Angeboten

### PDF-Generierung
- DomPDF-basiert, vollständig lokal (kein externer Dienst)
- Händler-Logo, Positionen, MwSt-Zeile, Fußzeile (HRB, USt-ID, Bankverbindung)
- Deutsche Zahlenformatierung (Komma als Dezimaltrenner)
- PDF nur über authentifizierten Admin-Endpoint abrufbar (nicht öffentlich)

### E-Mail-Versand
- Zwei Modi: **Automatisch** (E-Mail sofort nach Kalkulation) oder **Manuelle Freigabe**
- PDF als Anhang in der Kunden-E-Mail
- Direktlinks „Angebot annehmen" / „Angebot ablehnen" ohne Login (tokenbasiert)
- Shopware Mail-Template-System – im Admin editierbar

### Admin-Backend (Vue.js)
- Eigener Menüpunkt „B2B Angebote" in der Shopware Sidebar
- Übersichtsliste mit Statusampel, Filter und Freitextsuche
- Detailansicht: editierbarer KI-Text, PDF-Vorschau im Browser, Aktionsbuttons
- API-Key Test-Button
- Plugin-Einstellungen: Freigabemodus, Mindestmarge, Tonalität, E-Mail, Logo, Sichtbarkeit

### Status-Tracking
- Tokenbasierte Direktlinks für Kunden (kein Login nötig)
- Stündlicher Cron-Job markiert abgelaufene Angebote automatisch
- Händler-Benachrichtigung per E-Mail bei Annahme

---

## Status-Maschine

```
         ┌──────┐
         │ new  │ ← Anfrage eingegangen, KI kalkuliert
         └──┬───┘
            │ Händler gibt frei / Auto-Send
            ▼
         ┌──────┐
         │ sent │ ← Angebot beim Kunden
         └──┬───┘
  ┌─────────┼─────────┐
  ▼         ▼         ▼ (Gültigkeitsdatum überschritten)
┌──────────┐ ┌──────────┐ ┌─────────┐
│ accepted │ │ rejected │ │ expired │
└──────────┘ └──────────┘ └─────────┘
```

---

## Technischer Stack

| Schicht     | Technologie                              |
|-------------|------------------------------------------|
| Backend     | PHP 8.2+, Symfony 6.x (via Shopware)    |
| ORM / DB    | Shopware DAL (kein direktes SQL!)        |
| Admin-UI    | Vue.js 3 (Shopware Admin Component Lib) |
| Storefront  | Twig + Vanilla JS                        |
| PDF         | DomPDF ^2.0                              |
| KI-Adapter  | Anthropic Claude API / OpenAI API (BYOK)|
| Tests       | PHPUnit 10+ (Unit + Integration)         |
| Linter      | PHP CS Fixer (PSR-12)                    |

---

## Verzeichnisstruktur

```
QuoteAgent/
├── src/
│   ├── Controller/
│   │   └── QuoteRequestController.php
│   ├── Core/
│   │   ├── Quote/
│   │   │   ├── QuoteRequestEntity.php
│   │   │   ├── QuoteRequestDefinition.php
│   │   │   └── QuoteRequestRepository.php
│   │   ├── Pricing/
│   │   │   └── QuotePricingService.php
│   │   ├── AI/
│   │   │   ├── QuoteTextGeneratorService.php
│   │   │   ├── AiProviderInterface.php
│   │   │   ├── AnthropicAdapter.php
│   │   │   └── OpenAiAdapter.php
│   │   └── Pdf/
│   │       └── QuotePdfService.php
│   ├── Subscriber/
│   │   └── QuoteRequestSubscriber.php
│   ├── ScheduledTask/
│   │   └── QuoteExpirationTask.php
│   ├── Administration/          (Vue-Komponenten)
│   ├── Resources/
│   │   ├── views/               (Twig Templates)
│   │   ├── migration/
│   │   │   └── Migration1709000000CreateQuoteRequestTable.php
│   │   └── config/
│   │       └── services.xml
│   └── QuoteAgent.php
├── tests/
│   ├── Unit/
│   └── Integration/
├── docs/
│   ├── README.md                (PRD-Übersicht)
│   ├── PRD-001-plugin-foundation.md
│   ├── PRD-002-storefront-form.md
│   ├── PRD-003-pricing-service.md
│   ├── PRD-004-ai-text-generation.md
│   ├── PRD-005-pdf-generation.md
│   ├── PRD-006-email-dispatch.md
│   ├── PRD-007-admin-backend.md
│   └── PRD-008-quote-status-tracking.md
├── composer.json
├── CLAUDE.md
└── README.md
```

---

## Datenbankschema (`b2b_quote_request`)

| Spalte             | Typ         | Beschreibung                                       |
|--------------------|-------------|----------------------------------------------------|
| `id`               | BINARY(16)  | UUID, Primary Key                                  |
| `quote_number`     | VARCHAR(64) | Menschenlesbare Nummer (`QA-YYYY-XXXX`)            |
| `customer_id`      | BINARY(16)  | FK → `customer.id`                                 |
| `status`           | VARCHAR(32) | `new`, `sent`, `accepted`, `rejected`, `expired`   |
| `items`            | JSON        | Positionen: Produkt-ID, Menge, Preis, Marge        |
| `customer_message` | LONGTEXT    | Freitext des Kunden                                |
| `ai_text`          | LONGTEXT    | KI-generierter Angebotstext                        |
| `total_amount`     | FLOAT       | Kalkulierter Gesamtbetrag (netto)                  |
| `valid_until`      | DATETIME(3) | Gültigkeitsdatum                                   |
| `accept_token`     | VARCHAR(64) | Einmal-Token für Direktlink „Angebot annehmen"     |
| `pdf_path`         | VARCHAR(512)| Relativer Pfad zur generierten PDF-Datei           |
| `created_at`       | DATETIME(3) | Shopware-Standard                                  |
| `updated_at`       | DATETIME(3) | Shopware-Standard                                  |

---

## Lokale Entwicklung

```bash
# Plugin installieren und aktivieren (im Shopware-Root)
bin/console plugin:install --activate QuoteAgent

# Tests ausführen
composer test

# Code-Style prüfen (trocken)
composer cs-fix --dry-run

# Code-Style auto-fixen
composer cs-fix
```

---

## Implementierungs-Roadmap

```
Woche 1–2  │ PRD-001 │ Plugin-Skeleton, Entity, DB-Migration
Woche 3    │ PRD-003 │ QuotePricingService (DAL, Staffelpreise, Marge)
Woche 4    │ PRD-004 │ QuoteTextGeneratorService (Claude/GPT, BYOK)
Woche 5    │ PRD-005 │ PDF-Generierung (DomPDF)
           │ PRD-006 │ E-Mail-Versand (Shopware MailService)
Woche 6    │ PRD-007 │ Admin-UI (Liste, Detail, Einstellungen)
Woche 7    │ PRD-002 │ Storefront (Formular, Bestätigung, Kundenbereich)
           │ PRD-008 │ Status-Tracking (Direktlinks, Cron-Job)
Woche 8    │  –      │ Testing, Bugfixing, erster Testshop
```

Vollständige PRD-Details: [`docs/README.md`](docs/README.md)

---

## Preismodell

| Plan         | Preis       | Limit                              |
|--------------|-------------|------------------------------------|
| Starter      | 49 €/Monat  | 30 Angebote/Monat                  |
| Professional | 79 €/Monat  | Unbegrenzt + eigenes PDF-Branding  |

---

## Bewusst nicht in V1.0

| Feature                    | Geplant für |
|----------------------------|-------------|
| RAG / Vektordatenbank      | V2.0        |
| Lokale KI (Ollama/Llama)   | V2.0        |
| Multi-Language Angebote    | V2.0        |
| Analytics / Tracking       | V2.0        |
| Flow Builder Integration   | V1.5        |
| Automatische Bestellanlage | V3.0        |
| ERP-Anbindung              | V3.0        |

---

## Abhängigkeiten

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

## Sicherheits-Highlights

- API-Keys nur in Shopware `SystemConfigService`, masked angezeigt (letzte 4 Zeichen)
- `accept_token`: `bin2hex(random_bytes(32))` – 64 Zeichen kryptographisch sicher
- DomPDF: `isRemoteEnabled = false` (kein externes HTTP aus PDF-Rendering)
- PDF ausschließlich über authentifizierten Admin-Endpoint auslieferbar
- CSRF-Schutz auf allen Storefront-Formularen

---

## Lizenz

Proprietär – alle Rechte vorbehalten.
