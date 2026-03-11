# PRD-001: Plugin-Grundstruktur & Datenmodell

**Produkt:** QuoteAgent for Shopware 6
**Version:** V1.0
**Priorität:** P0 – Fundament (alles andere baut darauf auf)
**Entwicklungsphase:** Woche 1–2

---

## Problem

Shopware 6 bietet kein natives Quote-Management unterhalb des Evolve-Plans (2.400 €/Monat). Ein sauberes Custom-Entity-Fundament ist Voraussetzung für alle weiteren Features.

## Ziel

Ein vollständig registriertes Shopware-Plugin mit Custom Entity, Datenbankschema und Service-Container-Konfiguration, das als Basis für alle weiteren V1.0-Features dient.

---

## Scope V1.0

### In Scope

- Plugin-Skelett nach Shopware 6 Konventionen (`composer.json`, `Plugin.php`, `services.xml`)
- Custom Entity `QuoteRequestEntity` mit vollständiger DAL-Definition
- Datenbankmigrationsklasse für Tabelle `b2b_quote_request`
- Service-Registrierung für alle V1.0-Services (als Platzhalter)
- Plugin-Aktivierungs-/Deaktivierungslogik (Migration up/down)

### Out of Scope

- Admin-UI-Komponenten (PRD-007)
- Business-Logik in den Services (separate PRDs)
- Storefront-Templates (PRD-002)

---

## Technische Anforderungen

### Plugin-Verzeichnisstruktur

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
│   │   │   └── QuoteTextGeneratorService.php
│   │   └── Pdf/
│   │       └── QuotePdfService.php
│   ├── Subscriber/
│   │   └── QuoteRequestSubscriber.php
│   ├── Administration/          (Vue-Komponenten)
│   ├── Resources/
│   │   ├── views/               (Twig Templates)
│   │   ├── migration/
│   │   │   └── Migration1709000000CreateQuoteRequestTable.php
│   │   └── config/
│   │       └── services.xml
│   └── QuoteAgent.php
├── composer.json
└── README.md
```

### Datenbankschema `b2b_quote_request`

| Spalte              | Typ           | Beschreibung                                      |
|---------------------|---------------|---------------------------------------------------|
| `id`                | BINARY(16)    | UUID, Primary Key                                 |
| `quote_number`      | VARCHAR(64)   | Menschenlesbare Nummer (z.B. QA-2026-0001)        |
| `customer_id`       | BINARY(16)    | FK → `customer.id`                                |
| `status`            | VARCHAR(32)   | `new`, `sent`, `accepted`, `rejected`, `expired`  |
| `items`             | JSON          | Positionen: Produkt-ID, Menge, Preis, Marge       |
| `customer_message`  | LONGTEXT      | Freitext des Kunden                               |
| `ai_text`           | LONGTEXT      | KI-generierter Angebotstext                       |
| `total_amount`      | FLOAT         | Kalkulierter Gesamtbetrag (netto)                 |
| `valid_until`       | DATETIME(3)   | Gültigkeitsdatum                                  |
| `accept_token`      | VARCHAR(64)   | Einmal-Token für Direktlink "Angebot annehmen"    |
| `pdf_path`          | VARCHAR(512)  | Relativer Pfad zur generierten PDF-Datei          |
| `created_at`        | DATETIME(3)   | Shopware-Standard                                 |
| `updated_at`        | DATETIME(3)   | Shopware-Standard                                 |

### Entity-Felder (DAL)

- Alle DB-Spalten als `StringField`, `JsonField`, `FloatField`, `DateTimeField`
- Association: `ManyToOne` → `CustomerDefinition`
- `AutoIncrementField` für sequentielle Quote-Nummer

---

## Akzeptanzkriterien

- [ ] `bin/console plugin:install QuoteAgent` läuft fehlerfrei durch
- [ ] `bin/console plugin:activate QuoteAgent` führt Migration aus, Tabelle existiert in DB
- [ ] `bin/console plugin:deactivate QuoteAgent` + `plugin:uninstall --keep-user-data` funktioniert
- [ ] DAL-Repository kann Entities lesen/schreiben (Unit-Test)
- [ ] Alle Services sind im Container registriert (auch wenn noch leer)
- [ ] Keine Shopware-Core-Klassen überschrieben (Dekoratoren nur wenn explizit nötig)

---

## Offene Fragen

- Soll `quote_number` per DB-Sequence oder per PHP-Service generiert werden? → Empfehlung: PHP-Service mit `YYYYMMDD-XXXX`-Format
- Prefix konfigurierbar in Einstellungen? → V1.5
