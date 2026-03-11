# PRD-007: Admin-Backend (Angebotsübersicht, Detailansicht, Einstellungen)

**Produkt:** QuoteAgent for Shopware 6
**Version:** V1.0
**Priorität:** P1 – Händler braucht Kontrolle
**Entwicklungsphase:** Woche 6

---

## Problem

Ohne Admin-Interface ist der Händler blind. Er braucht einen zentralen Ort, um alle Angebote zu überblicken, Kalkulationen zu prüfen, KI-Text zu editieren und Freigaben zu erteilen.

## Ziel

Ein vollständiges Vue.js-Admin-Modul in Shopware 6, das dem Händler drei Ansichten bietet: Übersicht aller Anfragen, Detailansicht pro Anfrage und Plugin-Einstellungen – alles nativ eingebettet in das Shopware-Admin-Layout.

---

## Scope V1.0

### In Scope

- Eigener Menüpunkt "B2B Angebote" im Shopware Admin-Menü
- Angebotsübersicht (Liste mit Filter und Suche)
- Detailansicht mit editierbarem KI-Text, PDF-Vorschau, Aktionsbuttons
- Plugin-Einstellungen (alle Konfigurationsfelder aus Konzept)
- API-Key Test-Button

### Out of Scope

- Statistiken/Analytics-Dashboard – V2.0
- Bulk-Aktionen (Mehrfachauswahl) – V1.5
- Export als CSV/Excel – V1.5
- Angebote manuell erstellen (ohne Kundenanfrage) – V2.0

---

## Navigation

```
Shopware Admin Sidebar
└── B2B Angebote          (Icon: sw-icon name="regular-file-text")
    ├── Angebote          (route: quote-agent.list)
    └── Einstellungen     (route: quote-agent.settings)
```

---

## Ansicht 1: Angebotsübersicht

### UI-Elemente

```
[Suchfeld: Kundenname, Firma, Angebotsnr.]    [Status-Filter ▼]  [Zeitraum-Filter]

┌──────────────────────────────────────────────────────────────────────┐
│ ● Angebotsnr.  │ Kunde        │ Betrag      │ Datum      │ Status     │
├──────────────────────────────────────────────────────────────────────┤
│   QA-2026-0012 │ Müller GmbH  │ 4.820,00 € │ 11.03.26   │ 🟡 Neu     │
│   QA-2026-0011 │ Technik AG   │ 1.240,00 € │ 10.03.26   │ 🟢 Versend.│
│   QA-2026-0010 │ Bau KG       │   890,00 € │ 09.03.26   │ 🔵 Akzept. │
│   QA-2026-0009 │ Handel OHG   │ 2.100,00 € │ 08.03.26   │ 🔴 Abgel.  │
│   QA-2026-0008 │ Elektro GmbH │   450,00 € │ 01.03.26   │ ⏰ Abgel.  │
└──────────────────────────────────────────────────────────────────────┘
                                              Zeige 1-5 von 47  [< 1 2 3 >]
```

### Status-Ampel

| Status     | Farbe  | Hex       | Bedeutung                              |
|------------|--------|-----------|----------------------------------------|
| `new`      | Gelb   | `#FFB400` | KI hat kalkuliert, wartet auf Freigabe |
| `sent`     | Grün   | `#37D046` | Angebot ist beim Kunden                |
| `accepted` | Blau   | `#189EFF` | Kunde hat bestätigt                    |
| `rejected` | Rot    | `#FF4D4D` | Kunde hat abgelehnt                    |
| `expired`  | Grau   | `#9AA8B5` | Gültigkeitsdatum überschritten         |

### Paginierung & Filter

- Standard: 25 Einträge pro Seite
- Sortierung nach: Datum (Standard absteigend), Betrag, Status
- Freitextsuche: Kundenname, Firmenname, Angebotsnummer (Shopware `sw-simple-search-field`)

---

## Ansicht 2: Detailansicht

### Layout (zwei Spalten)

**Linke Spalte (2/3 Breite):**

```
Angebot QA-2026-0012                              Status: 🟡 Neu
─────────────────────────────────────────────────────────────────
KUNDENDATEN
Name:         Max Müller
Firma:        Müller Elektrotechnik GmbH
Kundengruppe: B2B Großhandel
E-Mail:       m.mueller@muellerelektro.de

KUNDENNACHRICHT
"Wir benötigen dringend 200 Stück für eine laufende Baustelle.
Können Sie Lieferung bis Freitag garantieren?"

POSITIONEN
┌────────────────────────────────────────────────────────────┐
│ Pos.│ Artikel        │ Menge │ EP netto │ GP netto │ Marge  │
├────────────────────────────────────────────────────────────┤
│  1  │ Kabel NYM 3x2.5│   200 │  4,50 €  │ 900,00 € │ 28% ✓ │
│  2  │ Verbindungsbox  │    50 │  7,80 €  │ 390,00 € │  8% ⚠ │← unter Mindestmarge
└────────────────────────────────────────────────────────────┘
               Netto:    1.290,00 €
               MwSt. 19%: 245,10 €
               Brutto:   1.535,10 €

KI-GENERIERTER ANGEBOTSTEXT
┌─────────────────────────────────────────────────────────────┐
│ Sehr geehrter Herr Müller,                                  │
│                                                             │
│ vielen Dank für Ihre Anfrage. Wir freuen uns, Ihnen...     │
│                                                             │
│ [editierbares Textarea]                                     │
└─────────────────────────────────────────────────────────────┘
[Text neu generieren ↺]

PDF-VORSCHAU
[PDF im Browser anzeigen →]
```

**Rechte Spalte (1/3 Breite):**

```
AKTIONEN
[✓ Freigeben & Senden]   ← primär, nur wenn Status "new"
[✗ Anfrage ablehnen]     ← sekundär

ANGEBOTSDATEN
Angebotsnummer: QA-2026-0012
Erstellt:       11.03.2026 09:14 Uhr
Gültig bis:     25.03.2026
```

### Margen-Warnung

Wenn mindestens eine Position unter der konfigurierten Mindestmarge liegt:

```
⚠ Hinweis: Position "Verbindungsbox" liegt mit 8% unter der
  konfigurierten Mindestmarge (15%). Bitte prüfen Sie das
  Angebot vor dem Versand.
```

---

## Ansicht 3: Plugin-Einstellungen

Einfaches Formular, kein Overwhelming:

### Sektion: Allgemein
- Plugin aktiv: `sw-field type="switch"`
- Freigabe-Modus: `sw-select` (Automatisch / Manuelle Freigabe)
- Gültigkeitsdauer: `sw-field type="number"` (Tage, Default: 14)
- Mindestmarge: `sw-field type="number"` (%, Default: 0)
- Preisbasis: `sw-select` (Staffelpreise / Nur Listenpreis)

### Sektion: KI-Einstellungen
- API-Anbieter: `sw-select` (Claude / OpenAI)
- API-Schlüssel: `sw-field type="password"` + [Verbindung testen]-Button
- Angebots-Tonalität: `sw-select` (Formal / Freundlich-locker / Technisch-präzise)
- Eigene Anweisungen: `sw-field type="textarea"` (optional)

### Sektion: E-Mail & PDF
- Absender-Name: `sw-field type="text"`
- Absender-E-Mail: `sw-field type="email"`
- E-Mail-Betreff: `sw-field type="text"` (mit Placeholder-Hinweis `{{offer_number}}`)
- Logo im PDF: `sw-media-upload-v2`
- PDF-Fußzeile: `sw-field type="textarea"`

### Sektion: Sichtbarkeit
- Sichtbar für Kundengruppen: `sw-entity-multi-select` (CustomerGroup)
- Benachrichtigungs-E-Mails: `sw-field type="text"` (kommagetrennte Adressen)

---

## API-Endpoints (Admin API)

| Method | Route                              | Beschreibung                          |
|--------|------------------------------------|---------------------------------------|
| GET    | `/api/quote-agent/quotes`          | Paginierte Liste                      |
| GET    | `/api/quote-agent/quotes/{id}`     | Einzelne Anfrage                      |
| PATCH  | `/api/quote-agent/quotes/{id}`     | KI-Text editieren                     |
| POST   | `/api/quote-agent/quotes/{id}/send`| Freigeben & senden                    |
| POST   | `/api/quote-agent/quotes/{id}/regenerate` | KI-Text neu generieren         |
| GET    | `/api/quote-agent/quotes/{id}/pdf` | PDF als Stream                        |
| POST   | `/api/quote-agent/test-api-key`    | API-Key validieren                    |

---

## Akzeptanzkriterien

- [ ] Menüpunkt "B2B Angebote" erscheint in der Admin-Sidebar
- [ ] Statusampel zeigt korrekte Farben für alle 5 Status-Werte
- [ ] Freitextsuche findet Anfragen nach Kundenname und Angebotsnummer
- [ ] KI-Text ist im Detailview editierbar und wird nach PATCH gespeichert
- [ ] "Neu generieren" überschreibt den Text (mit Bestätigungs-Dialog: "Text wird überschrieben")
- [ ] Margen-Warnung erscheint wenn mindestens eine Position unter Mindestmarge
- [ ] "Freigeben & Senden" ist nur klickbar wenn Status "new" oder "rejected"
- [ ] API-Key Test-Button zeigt "Verbindung erfolgreich ✓" oder Fehlermeldung
- [ ] Logo-Upload akzeptiert PNG, JPG, SVG; max. 2 MB
- [ ] Alle Einstellungen werden korrekt in Shopware SystemConfig gespeichert
