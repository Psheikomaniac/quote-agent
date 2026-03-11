# PRD-006: E-Mail-Versand & Freigabe-Workflow

**Produkt:** QuoteAgent for Shopware 6
**Version:** V1.0
**Priorität:** P1 – Direkte Kundenkommunikation
**Entwicklungsphase:** Woche 5

---

## Problem

Das Angebot muss beim Kunden ankommen. Der Händler braucht dabei Kontrolle – besonders am Anfang, wenn er dem System noch nicht vertraut. Gleichzeitig ist der volle Automatismus (Auto-Send) das Kernversprechen für erfahrene Nutzer.

## Ziel

Ein E-Mail-Versandmechanismus über den nativen Shopware `MailService`, mit konfigurierbarem Freigabe-Modus (automatisch oder manuell), PDF als Anhang und einem Tracking-Link für den Kunden.

---

## Scope V1.0

### In Scope

- Zwei Modi: Auto-Send und Manuelle Freigabe (per Admin-Einstellung)
- PDF-Anhang in der Kunden-E-Mail
- E-Mail-Benachrichtigung an Händler bei neuer Anfrage
- Direktlink "Angebot annehmen" in der Kunden-E-Mail (tokenbasiert, kein Login nötig)
- Bestätigungs-E-Mail an Kunden nach Annahme/Ablehnung
- Shopware Mail-Template-System (editierbar im Admin)

### Out of Scope

- E-Mail-Tracking (Öffnungsrate, Klick-Tracking) – V2.0
- Automatisches Nachfassen (Reminder-E-Mail) – V1.5
- Mehrsprachige E-Mail-Templates – V2.0
- CC/BCC-Konfiguration – V1.5

---

## Workflow

### Modus A: Automatischer Versand

```
Anfrage eingeht
    ↓
Preiskalkulation (synchron)
    ↓
KI-Textgenerierung (synchron)
    ↓
PDF-Generierung (synchron)
    ↓
E-Mail an Kunden versenden (PDF als Anhang)
    ↓
Status: "sent"
    ↓
Benachrichtigung an Händler ("Neue Anfrage wurde automatisch bearbeitet")
```

### Modus B: Manuelle Freigabe (empfohlen für Erstnutzer)

```
Anfrage eingeht
    ↓
Preiskalkulation + KI-Text + PDF (synchron)
    ↓
Status: "new" (wartet auf Freigabe)
    ↓
Benachrichtigung an Händler: "Neue Anfrage – bitte prüfen"
    ↓
Händler öffnet Admin, prüft Kalkulation + KI-Text
    ↓
Händler klickt "Freigeben & Senden"
    ↓
E-Mail an Kunden, Status: "sent"
```

---

## Technische Anforderungen

### Mail-Templates

Zwei neue Shopware Mail-Templates (im Admin editierbar unter "E-Mail-Templates"):

#### 1. `quote_agent_quote_sent` – Angebot an Kunden

```
Betreff: {config.emailSubject} (Default: "Ihr Angebot Nr. {{offer_number}}")

Inhalt:
Sehr geehrte/r {{customer.firstName}} {{customer.lastName}},

[KI-generierter Angebotstext oder Händler-eigener Text]

Dieses Angebot ist gültig bis: {{valid_until}}

► Angebot annehmen: {{accept_link}}
► Angebot ablehnen: {{reject_link}}

Mit freundlichen Grüßen,
{{config.senderName}}

--
Angebotsnummer: {{offer_number}}
Anhang: Angebot_{{offer_number}}.pdf
```

#### 2. `quote_agent_new_request` – Benachrichtigung an Händler

```
Betreff: [QuoteAgent] Neue Anfrage von {{customer.company}}

Neue Angebotsanfrage eingegangen.
Kunde: {{customer.firstName}} {{customer.lastName}} ({{customer.company}})
Betrag: {{total_amount}} € netto
Gesamtpositionen: {{items_count}}

► Im Admin ansehen: {{admin_link}}
```

### Tracking-Links

```php
// accept_token = zufälliger 64-Zeichen-String (generiert beim Erstellen der Entity)
$acceptLink = $this->router->generate(
    'frontend.quote.accept',
    ['token' => $quoteRequest->getAcceptToken()],
    RouterInterface::ABSOLUTE_URL
);

$rejectLink = $this->router->generate(
    'frontend.quote.reject',
    ['token' => $quoteRequest->getAcceptToken()],
    RouterInterface::ABSOLUTE_URL
);
```

- Token ist einmalig gültig (nach Verwendung wird Status gesetzt, Link funktioniert weiter für Status-Anzeige)
- Kein Login erforderlich (UX-Anforderung aus Konzept)
- HTTPS-Only (Shopware-Standard)

### Freigabe-Controller (Admin API)

```
Route: POST /api/quote-agent/quotes/{id}/send
Auth: Bearer Token
Body: {} (leer)
Response: { "status": "sent" }
```

Führt aus: E-Mail versenden + Status auf `sent` setzen + Timestamp `sent_at` speichern.

### Shopware MailService-Integration

```php
$this->mailService->send(
    [
        'recipients'   => [$customer->getEmail() => $customer->getFirstName()],
        'senderName'   => $config->getSenderName(),
        'senderEmail'  => $config->getSenderEmail(),
        'subject'      => $subject,
        'contentHtml'  => $htmlContent,
        'contentPlain' => $plainContent,
        'attachments'  => [
            [
                'content'  => $pdfContent,
                'fileName' => "Angebot_{$quoteNumber}.pdf",
                'mimeType' => 'application/pdf',
            ],
        ],
    ],
    $context,
    $templateData
);
```

---

## Akzeptanzkriterien

- [ ] Im Modus "Manuell": Anfrage landet in Admin mit Status "Neu", keine E-Mail geht raus
- [ ] Im Modus "Automatisch": E-Mail wird direkt nach Kalkulation + KI + PDF versandt
- [ ] PDF ist korrekt als Anhang in der Kunden-E-Mail
- [ ] Direktlink "Angebot annehmen" funktioniert ohne Login
- [ ] Händler-Benachrichtigung enthält Admin-Direktlink zur Anfrage
- [ ] Mail-Templates sind im Shopware Admin editierbar
- [ ] E-Mail-Versandfehler (SMTP-Problem) führt nicht zu Datenverlust – Status bleibt "new"
- [ ] Absender-Name und Absender-E-Mail aus Plugin-Einstellungen werden korrekt genutzt
