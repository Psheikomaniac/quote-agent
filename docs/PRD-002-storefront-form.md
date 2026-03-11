# PRD-002: Storefront – Angebotsanfrage-Formular

**Produkt:** QuoteAgent for Shopware 6
**Version:** V1.0
**Priorität:** P0 – Ohne Formular kein Einstiegspunkt
**Entwicklungsphase:** Woche 7

---

## Problem

B2B-Kunden haben heute keinen integrierten Weg, Angebotsanfragen direkt im Shop zu stellen. Sie greifen zu Telefon oder E-Mail – was manuellen Aufwand auf Händlerseite erzeugt und zu langen Reaktionszeiten führt.

## Ziel

Ein eingebettetes, nur für B2B-Kundengruppen sichtbares Angebotsformular mit drei Einstiegspunkten, das Anfragen vollständig digitalisiert und automatisch in die Datenbank schreibt.

---

## Scope V1.0

### In Scope

- Drei Einstiegspunkte (Produktseite, Listing, Standalone-Seite)
- Formular mit Produktsuche, Mengenangaben, Nachricht, Liefertermin
- Vorausfüllung aus Kundenkonto-Daten
- Bestätigungsseite nach Absenden
- Kundenbereich "Meine Angebote" mit Statusübersicht
- Sichtbarkeit nur für konfigurierte B2B-Kundengruppen

### Out of Scope

- Gast-Anfragen (Login erforderlich)
- Mehrsprachige Formularbeschriftungen (V2.0)
- Dateianhänge/Spezifikationsdokumente (V2.0)

---

## User Story

**Als** eingeloggter B2B-Kunde
**möchte ich** direkt im Shop eine Angebotsanfrage für mehrere Produkte stellen können
**damit ich** nicht mehr anrufen oder separate E-Mails schreiben muss.

---

## Technische Anforderungen

### Einstiegspunkte

#### 1. Produktdetailseite
```twig
{# Template: storefront/page/product-detail/buy-widget.html.twig #}
{# Nur wenn: Kunde eingeloggt + Mitglied einer B2B-Kundengruppe #}
<a href="{{ path('frontend.quote.request', {productId: page.product.id}) }}"
   class="btn btn-outline-secondary btn-block">
    {{ "quoteAgent.storefront.requestQuoteBtn"|trans }}
</a>
```
Erscheint ab konfigurierbarer Mindestmenge (Standard: 1, konfigurierbar im Admin).

#### 2. Kategorie-/Listingseite
Kleiner "Angebot"-Link neben "In den Warenkorb"-Button. Per Twig-Block-Override.

#### 3. Standalone-Seite `/angebot-anfragen`
Route: `QuoteRequestController::requestForm()` – verlinkbar aus Navigation und Footer.

### Controller

```
Route: POST /quote-agent/request
Name: frontend.quote.request.submit
Class: QuoteRequestController
Method: submitAction(Request $request, SalesChannelContext $context): Response
```

**Validierung:**
- Mindestens eine Position mit Produkt-ID und Menge > 0
- Produkt-IDs müssen im aktuellen Sales-Channel existieren
- Menge: ganzzahlig, 1–9999
- Nachricht: max. 2.000 Zeichen
- CSRF-Token (Shopware-Standard)

**Nach erfolgreichem Submit:**
1. `QuoteRequestEntity` in DB speichern (Status: `new`)
2. Event `QuoteRequestCreatedEvent` dispatchen (Subscriber reagiert)
3. Redirect zu Bestätigungsseite mit Anfrage-Nummer

### Formularfelder

| Feld                  | Typ            | Pflicht | Vorausgefüllt          |
|-----------------------|----------------|---------|------------------------|
| Produktsuche          | Autocomplete   | Ja      | Wenn von Produktseite  |
| Menge je Position     | Number         | Ja      | –                      |
| Weitere Position +    | Button         | –       | –                      |
| Wunsch-Liefertermin   | Date           | Nein    | –                      |
| Lieferadresse         | Select/Formular| Nein    | Standard-Lieferadresse |
| Nachricht             | Textarea       | Nein    | –                      |
| Kontaktdaten          | Readonly-Block | –       | Aus Kundenkonto        |
| AGB-Checkbox          | Checkbox       | Ja      | –                      |

### Produktsuche (Autocomplete)

- Endpoint: `GET /quote-agent/product-search?term={query}`
- Suche in: Produktname, Artikelnummer (Prefix-Match)
- Nur aktive Produkte im aktuellen Sales-Channel
- Rückgabe: JSON `[{id, name, productNumber, price}]`
- Max. 10 Ergebnisse

### Kundenbereich "Meine Angebote"

Route: `GET /account/quote-requests`
Tabelle mit: Anfrage-Nr., Datum, Betrag, Status-Badge, Link zur Detailansicht.

---

## Akzeptanzkriterien

- [ ] Formular ist für nicht eingeloggte Nutzer und B2C-Kunden unsichtbar
- [ ] Sichtbarkeit steuert sich über konfigurierte Kundengruppen (Admin-Einstellung)
- [ ] Nach Submit landet Entity in DB mit Status `new`
- [ ] Bestätigungsseite zeigt Anfrage-Nummer
- [ ] Produktsuche liefert nur Sales-Channel-konforme Produkte
- [ ] Ungültige Produktmengen (0, negativ, nicht-ganzzahlig) werden client- und serverseitig abgefangen
- [ ] CSRF-Schutz aktiv
- [ ] Kundenbereich zeigt alle Anfragen des eingeloggten Kunden mit korrektem Status
