# PRD-003: Automatische Preiskalkulation

**Produkt:** QuoteAgent for Shopware 6
**Version:** V1.0
**Priorität:** P0 – Kern-Differenzierung
**Entwicklungsphase:** Woche 3

---

## Problem

Das zentrale Versprechen des Plugins ist "kein manuelles Eingreifen". Das setzt voraus, dass die Preiskalkulation vollständig deterministisch und korrekt ist – basierend auf den tatsächlichen Shopware-Konditionen des Kunden.

## Ziel

Ein PHP-Service, der für jede Angebotsanfrage automatisch den korrekten Angebotspreis berechnet – inklusive Staffelpreisen, Kundengruppen-Konditionen und Mindestmargen-Prüfung. Keine KI-Beteiligung bei der Berechnung.

---

## Architekturprinzip: Deterministik vor KI

```
Symfony berechnet          KI formuliert
──────────────────         ─────────────────────
Produktpreise (DAL)   →    Professionellen Text
Staffelrabatte             Auf Basis fertiger Zahlen
Kundengruppen-Preis        Kein Rechnen, kein Raten
Mindestmarge-Check         In gewünschter Tonalität
MwSt-Berechnung
```

**Die KI erhält ausschließlich fertig berechnete Zahlen als JSON. Sie rechnet niemals selbst.**

---

## Scope V1.0

### In Scope

- Preisauflösung aus Shopware DAL (Listenpreis + Staffelpreise)
- Kundengruppen-spezifische Preise berücksichtigen
- Margenberechnung pro Position (Einkaufspreis → Verkaufspreis)
- Mindestmarge-Prüfung mit Markierung (rot/grün)
- MwSt.-Berechnung (netto/brutto, abhängig von Kundengruppen-Konfiguration)
- Konfigurierbarer Preisbasis-Modus (Staffelpreise vs. nur Listenpreis)

### Out of Scope

- Dynamische Rabattregeln per Promotion-Engine (zu komplex für V1)
- Währungsumrechnung (V2.0)
- Sonderkonditionen per Freitext-Verhandlung (V2.0)

---

## User Story

**Als** Händler
**möchte ich** dass jede Angebotsanfrage automatisch mit den korrekten Kundenpreisen kalkuliert wird
**damit ich** keine Taschenrechner oder Excel-Tabellen mehr benötige.

---

## Technische Anforderungen

### Service-Signatur

```php
// src/Core/Pricing/QuotePricingService.php

class QuotePricingService
{
    public function calculate(
        QuoteRequestEntity $quoteRequest,
        SalesChannelContext $context
    ): QuoteCalculationResult;
}
```

### `QuoteCalculationResult` (Value Object)

```php
class QuoteCalculationResult
{
    /** @var QuoteLineItem[] */
    public readonly array $lineItems;
    public readonly float $totalNet;
    public readonly float $totalGross;
    public readonly float $taxAmount;
    public readonly float $taxRate;       // z.B. 19.0
    public readonly bool  $belowMinMargin; // true = rot markieren
    public readonly float $lowestMargin;  // kleinste Marge aller Positionen in %
}

class QuoteLineItem
{
    public readonly string $productId;
    public readonly string $productName;
    public readonly string $productNumber;
    public readonly int    $quantity;
    public readonly float  $unitPriceNet;
    public readonly float  $unitPurchasePrice; // Einkaufspreis aus DAL
    public readonly float  $totalNet;
    public readonly float  $marginPercent;
    public readonly bool   $belowMinMargin;
}
```

### Preisauflösungs-Logik

```
1. Lade Produkt via ProductRepository (mit PriceCollection)
2. Prüfe Preisbasis-Modus (Plugin-Einstellung):
   a. "Staffelpreise": Wähle günstigsten Staffelpreis für gegebene Menge
      → QuantityPriceDefinition + PriceCalculator (Shopware-native)
   b. "Nur Listenpreis": Immer price[0] unabhängig von Menge
3. Wende Kundengruppen-Netto/Brutto-Einstellung an
4. Berechne Marge: (Verkaufspreis - Einkaufspreis) / Verkaufspreis * 100
5. Vergleiche Marge mit Mindestmarge-Einstellung
```

### DAL-Abfragen

```php
// Produkt mit Preisen laden
$criteria = new Criteria([$productId]);
$criteria->addAssociation('prices');          // Staffelpreise
$criteria->addAssociation('purchasePrices'); // Einkaufspreise

$product = $this->productRepository->search($criteria, $context)->first();
```

**Wichtig:** Shopware's nativen `SalesChannelProductPriceCalculator` verwenden, nicht selbst rechnen – damit Promotions und Regeln korrekt greifen.

### Mindestmarge-Prüfung

- Mindestmarge in % aus Plugin-Systemconfig lesen
- Wenn `marginPercent < minMargin` → `belowMinMargin = true`
- Gesamtangebot: `belowMinMargin = true` wenn mindestens eine Position darunter
- Im Admin: Marge-Spalte rot einfärben, Warnung anzeigen
- Plugin sendet trotzdem – Händler entscheidet (Sicherheitsnetz, kein Blocker)

---

## Akzeptanzkriterien

- [ ] Staffelpreise werden korrekt für die angegebene Menge aufgelöst
- [ ] Kundengruppen-Nettopreise (B2B ohne MwSt.) werden korrekt angewendet
- [ ] Marge wird pro Position berechnet und angezeigt
- [ ] Positionen unterhalb der Mindestmarge sind rot markiert (Admin-UI)
- [ ] Unit-Tests für: Staffelpreisauflösung, Margenberechnung, Mindestmarge-Prüfung
- [ ] Kein direktes SQL – ausschließlich Shopware DAL
- [ ] Service ist zustandslos (kein Caching, kein State zwischen Aufrufen)

---

## Offene Fragen

- Soll die Mindestmarge ein hartes Limit sein (kein Versand) oder nur eine Warnung? → **Konzept: Nur Warnung** – Händler hat Kontrolle
- Was passiert wenn ein Produkt keinen Einkaufspreis hat? → Marge = null, keine Warnung
