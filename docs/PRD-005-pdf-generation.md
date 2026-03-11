# PRD-005: PDF-Generierung

**Produkt:** QuoteAgent for Shopware 6
**Version:** V1.0
**Priorität:** P1 – Das sichtbare Ergebnis für den Händler
**Entwicklungsphase:** Woche 5

---

## Problem

Ein Angebot, das nur als E-Mail-Text versandt wird, wirkt unprofessionell. B2B-Kunden erwarten ein gestaltetes PDF-Dokument mit Briefkopf, Firmenlogo und vollständigen Angabene – äquivalent zu einem Word-Angebot, aber automatisch und konsistent.

## Ziel

Automatische Generierung eines professionell gestalteten PDF-Angebotsdokuments mit Händler-Branding, Positionen, Preisen und rechtlichen Pflichtangaben – vollständig innerhalb des Shopware-Plugins ohne externe Dienste.

---

## Scope V1.0

### In Scope

- DomPDF-basierte PDF-Generierung aus HTML-Template
- Händler-Logo (Upload im Admin)
- Angebotspositionen mit Einzelpreis, Menge, Gesamtpreis, MwSt.
- KI-generierter Angebotstext eingebettet
- Händler-Stammdaten in Fußzeile (HRB, USt-ID, Bankverbindung etc.)
- PDF-Vorschau direkt im Admin-Browser
- PDF-Datei als E-Mail-Anhang

### Out of Scope

- Individuelles PDF-Design per Theme/CSS-Editor (V2.0 – Professional Plan)
- Mehrsprachige PDF-Vorlagen (V2.0)
- Digitale Signatur / qualifizierte elektronische Signatur (V3.0)
- Stempel / Wasserzeichen "Entwurf" (V1.5)

---

## Technische Anforderungen

### Service-Signatur

```php
// src/Core/Pdf/QuotePdfService.php

class QuotePdfService
{
    public function generate(
        QuoteRequestEntity $quoteRequest,
        QuoteCalculationResult $calculation,
        PluginConfig $config
    ): string; // Gibt absoluten Dateipfad zurück

    public function getContent(
        QuoteRequestEntity $quoteRequest,
        QuoteCalculationResult $calculation,
        PluginConfig $config
    ): string; // Gibt PDF als binary string zurück (für E-Mail-Anhang)
}
```

### DomPDF-Integration

```php
// composer.json Abhängigkeit
"dompdf/dompdf": "^2.0"
```

```php
use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', false); // Kein externes HTTP für Sicherheit
$options->set('defaultFont', 'DejaVu Sans'); // UTF-8 + Umlaute
$options->set('dpi', 150);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
```

### PDF-Speicherort

```
{shopware_root}/var/storage/quote-agent/pdfs/{quote_number}.pdf
```

- Verzeichnis wird bei Plugin-Aktivierung erstellt
- Nicht öffentlich über Web erreichbar (außerhalb `public/`)
- Download nur über Admin-Controller mit Authentifizierung

### HTML-Template-Struktur

```
┌─────────────────────────────────────────────┐
│  [LOGO]              Angebot                │
│  Händlername                                │
│  Adresse                                    │
├─────────────────────────────────────────────┤
│  An:                        Angebotsnr.: QA-2026-0001
│  Kundenname                 Datum: 11.03.2026
│  Firma                      Gültig bis: 25.03.2026
│  Adresse                                    │
├─────────────────────────────────────────────┤
│  [KI-generierter Angebotstext]              │
├─────────────────────────────────────────────┤
│  Pos. │ Artikelnr. │ Bezeichnung │ Menge │ EP netto │ GP netto │
│   1   │ ART-001    │ Produktname │  100  │ 12,50 €  │ 1.250,00 €│
│   2   │ ART-002    │ Produktname │   50  │  8,00 €  │   400,00 €│
├─────────────────────────────────────────────┤
│                         Nettobetrag: 1.650,00 €   │
│                         MwSt. 19%:    313,50 €    │
│                         Gesamtbetrag: 1.963,50 €  │
├─────────────────────────────────────────────┤
│  [PDF-Fußzeile: HRB, USt-ID, Bankverbindung] │
└─────────────────────────────────────────────┘
```

### Logo-Einbindung

```php
// Logo als base64 einbetten (kein Remote-Call nötig, Sicherheit)
$logoPath = $config->getPdfLogoPath();
if ($logoPath && file_exists($logoPath)) {
    $logoBase64 = base64_encode(file_get_contents($logoPath));
    $logoMimeType = mime_content_type($logoPath);
    $logoDataUri = "data:{$logoMimeType};base64,{$logoBase64}";
}
```

Unterstützte Formate: PNG, JPG, SVG. Max. Dateigröße: 2 MB.

### Zahlenformatierung

- Deutsche Locale: `number_format($value, 2, ',', '.')` → `1.963,50`
- Währungssymbol: `€` immer nachgestellt (DE-Standard)
- Artikelnummer: unveränderter String aus Shopware

### Admin: PDF-Vorschau

```
Route: GET /api/quote-agent/quotes/{id}/pdf-preview
Auth: Bearer Token (Admin-Session)
Response: Content-Type: application/pdf
          Content-Disposition: inline; filename="angebot-{number}.pdf"
```

Browser öffnet PDF direkt im Tab.

---

## Akzeptanzkriterien

- [ ] PDF wird nach KI-Textgenerierung automatisch erstellt und Pfad in Entity gespeichert
- [ ] Logo erscheint im PDF (wenn hochgeladen)
- [ ] Alle Positionen mit korrekten Preisen (aus `QuoteCalculationResult`)
- [ ] MwSt.-Zeile korrekt (Betrag und Prozentsatz)
- [ ] Fußzeile-Text aus Admin-Einstellung erscheint
- [ ] Umlaute (ä, ö, ü, ß) korrekt dargestellt
- [ ] PDF-Vorschau im Admin öffnet sich inline (kein Download-Zwang)
- [ ] PDF nicht über öffentliche URL abrufbar (nur über authentifizierten Admin-Endpoint)
- [ ] Fehler bei PDF-Generierung führt nicht zum Verlust der Anfrage (graceful degradation)
