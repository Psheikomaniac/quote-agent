# QuoteAgent for Shopware 6 – PRD-Übersicht V1.0

KI-gestützte B2B-Angebotserstellung für Shopware 6 Rise Plan & Community Edition.
**Zielgruppe:** KMU mit 5–50 MA, 5–50 Angebotsanfragen/Monat, Budget 49–79 €/Monat.

---

## PRDs V1.0

| Nr.    | Titel                            | Priorität | Phase       | Abhängigkeiten |
|--------|----------------------------------|-----------|-------------|----------------|
| [001](PRD-001-plugin-foundation.md)  | Plugin-Grundstruktur & Datenmodell | P0 | Woche 1–2 | – |
| [002](PRD-002-storefront-form.md)    | Storefront Angebotsformular      | P0        | Woche 7     | 001            |
| [003](PRD-003-pricing-service.md)    | Automatische Preiskalkulation    | P0        | Woche 3     | 001            |
| [004](PRD-004-ai-text-generation.md) | KI-Textgenerierung & BYOK        | P1        | Woche 4     | 003            |
| [005](PRD-005-pdf-generation.md)     | PDF-Generierung (DomPDF)         | P1        | Woche 5     | 003, 004       |
| [006](PRD-006-email-dispatch.md)     | E-Mail-Versand & Freigabe        | P1        | Woche 5     | 005            |
| [007](PRD-007-admin-backend.md)      | Admin-Backend (Vue.js)           | P1        | Woche 6     | 003, 004, 005  |
| [008](PRD-008-quote-status-tracking.md) | Angebots-Status & Tracking    | P2        | Woche 7     | 006            |

---

## Entwicklungs-Roadmap

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

---

## Kernarchitektur-Prinzip

```
Symfony berechnet (deterministisch)    KI formuliert (Text)
───────────────────────────────        ──────────────────────
Staffelpreise aus DAL             →    Professionellen Angebotstext
Kundengruppen-Konditionen              Auf Basis fertiger Zahlen
Mindestmarge-Prüfung                   In gewünschter Tonalität
MwSt-Berechnung                        Auf Deutsch
```

**Die KI rechnet niemals selbst. Sie formuliert nur.**

---

## Bewusst NICHT in V1.0

| Feature                   | Version |
|---------------------------|---------|
| RAG / Vektordatenbank     | V2.0    |
| Lokale KI (Ollama/Llama)  | V2.0    |
| Flow Builder Integration  | V1.5    |
| ERP-Anbindung             | V3.0    |
| Multi-Language Angebote   | V2.0    |
| Analytics / Tracking      | V2.0    |
| Automatische Bestellanlage| V3.0    |

---

## Preismodell V1.0

| Plan         | Preis       | Limit                         |
|--------------|-------------|-------------------------------|
| Starter      | 49 €/Monat  | 30 Angebote/Monat             |
| Professional | 79 €/Monat  | Unbegrenzt + eigenes PDF-Branding |
