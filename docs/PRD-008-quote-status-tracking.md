# PRD-008: Angebots-Status & Kunden-Tracking

**Produkt:** QuoteAgent for Shopware 6
**Version:** V1.0
**Priorität:** P2 – Schließt den Kreislauf
**Entwicklungsphase:** Woche 7 (parallel zum Storefront)

---

## Problem

Das Angebot ist versandt – aber was passiert danach? Ohne Tracking weiß der Händler nicht ob der Kunde das Angebot gelesen, angenommen oder still ignoriert hat. Der Kunde braucht einen einfachen Weg zu reagieren, ohne sich neu einloggen zu müssen.

## Ziel

Ein einfaches, tokenbasiertes Tracking-System, das dem Kunden erlaubt, Angebote per Direktlink anzunehmen oder abzulehnen – und dem Händler den aktuellen Status sichtbar macht. Geschlossener Kreislauf ohne manuelle Nachfrage.

---

## Scope V1.0

### In Scope

- Tokenbasierter Direktlink "Angebot annehmen" (kein Login nötig)
- Tokenbasierter Direktlink "Angebot ablehnen" (kein Login nötig)
- Status-Updates in der DB (`accepted`, `rejected`, `expired`)
- Bestätigungsseite nach Annahme/Ablehnung
- E-Mail-Benachrichtigung an Händler bei Annahme
- Automatische Ablauf-Markierung (Cron-basiert, Status `expired`)
- Kundenbereich "Meine Angebote": alle eigenen Anfragen mit Status

### Out of Scope

- Verhandlungs-Workflow (Gegenangebot) – V2.0
- Automatische Bestellanlage nach Annahme – V3.0
- Push-Benachrichtigungen – V2.0
- Automatische Erinnerungs-E-Mail an Kunden – V1.5

---

## Status-Maschine

```
                    ┌──────┐
                    │ new  │ ← Anfrage eingegangen, KI kalkuliert
                    └──┬───┘
                       │ Händler gibt frei (manuell) oder Auto-Send
                       ▼
                    ┌──────┐
                    │ sent │ ← Angebot beim Kunden
                    └──┬───┘
           ┌───────────┼───────────┐
           ▼           ▼           ▼ (Gültigkeitsdatum überschritten)
      ┌──────────┐ ┌──────────┐ ┌─────────┐
      │ accepted │ │ rejected │ │ expired │
      └──────────┘ └──────────┘ └─────────┘
```

**Erlaubte Übergänge:**
- `new` → `sent` (Freigabe)
- `new` → `rejected` (Händler lehnt ab)
- `sent` → `accepted` (Kunde nimmt an)
- `sent` → `rejected` (Kunde lehnt ab)
- `sent` → `expired` (Cron-Job)

---

## Technische Anforderungen

### Token-Generierung

```php
// Bei Erstellung der QuoteRequestEntity
$acceptToken = bin2hex(random_bytes(32)); // 64 Zeichen hex, kryptographisch sicher
```

- Ein Token für sowohl Annahme als auch Ablehnung (Aktion per Route-Parameter)
- Token wird **nicht** invalidiert nach Verwendung – Seite bleibt aufrufbar (zeigt Status an)
- Token wird **nicht** in Logs geschrieben

### Kunden-Direktlinks (Storefront, kein Login)

#### Annahme-Route

```
GET /angebot/annehmen/{token}
Route: frontend.quote.accept
Controller: QuoteRequestController::acceptAction()
```

**Ablauf:**
1. Token in DB nachschlagen
2. Wenn Status nicht `sent` → Fehlerseite ("Dieses Angebot ist nicht mehr verfügbar")
3. Status auf `accepted` setzen, `accepted_at` Timestamp speichern
4. E-Mail an Händler: "Angebot QA-2026-0012 wurde angenommen!"
5. Bestätigungsseite anzeigen

#### Ablehnungs-Route

```
GET /angebot/ablehnen/{token}
Route: frontend.quote.reject
Controller: QuoteRequestController::rejectAction()
```

**Ablauf:** Analog zu Annahme, aber Status `rejected`.

### Bestätigungsseiten (Twig Templates)

#### Nach Annahme

```
✓ Angebot angenommen

Vielen Dank! Wir haben Ihre Annahme von Angebot QA-2026-0012
erhalten und werden uns schnellstmöglich mit Ihnen in Verbindung setzen.

Betrag: 1.963,50 € (inkl. MwSt.)

[Zurück zum Shop]
```

#### Nach Ablehnung

```
Angebot abgelehnt

Sie haben Angebot QA-2026-0012 abgelehnt.
Falls Sie Fragen haben oder ein neues Angebot wünschen,
kontaktieren Sie uns gerne.

[Kontakt aufnehmen]  [Zurück zum Shop]
```

### Cron-Job: Ablauf-Markierung

```php
// Shopware Scheduled Task
class QuoteExpirationTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'quote_agent.expire_quotes';
    }

    public static function getDefaultInterval(): int
    {
        return 3600; // Stündlich
    }
}
```

**Logik:** Alle Angebote mit Status `sent` und `valid_until < now()` → Status `expired` setzen.

### Händler-Benachrichtigung bei Annahme

E-Mail via `quote_agent_quote_accepted` Template:

```
Betreff: [QuoteAgent] Angebot angenommen – {customer.company}

Ihr Angebot QA-2026-0012 wurde von Müller Elektrotechnik GmbH angenommen.

Betrag:  1.963,50 € (inkl. MwSt.)
Annahme: 11.03.2026 14:32 Uhr

► Im Admin ansehen: {admin_link}

Bitte legen Sie die Bestellung manuell an.
```

### Kundenbereich "Meine Angebote"

```
Route: GET /account/quote-requests
Route: GET /account/quote-requests/{id}
```

Liste aller eigenen Anfragen (gefiltert nach `customer_id`):

```
Meine Angebote

Angebotsnr.   │ Datum      │ Betrag       │ Status        │
QA-2026-0012  │ 11.03.26   │ 1.963,50 €   │ 🟡 In Bearbeit│ [Details]
QA-2026-0010  │ 09.03.26   │   890,00 €   │ 🔵 Angenommen │ [Details]
```

**Detailseite:** Schreibgeschützte Ansicht: Positionen, KI-Text, Status, Direktlinks (nur wenn Status `sent`).

---

## Akzeptanzkriterien

- [ ] Direktlink "Angebot annehmen" funktioniert ohne Login (Browser-Direktaufruf)
- [ ] Nach Annahme: Status in DB ist `accepted`, Timestamp gesetzt
- [ ] Nach Annahme: Händler-E-Mail wird innerhalb von 30 Sekunden zugestellt
- [ ] Abgelaufene Angebote (Cron-Job) sind im Admin als "Abgelaufen" markiert
- [ ] Ungültiger Token zeigt informative Fehlerseite (kein 500-Fehler)
- [ ] Token im URL ist nicht erratbar (min. 64 Zeichen random hex)
- [ ] Kundenbereich zeigt nur eigene Angebote (Sicherheit: customer_id-Check)
- [ ] Doppelklick auf "Annehmen" idempotent – kein doppelter Status-Wechsel
