# PRD-004: KI-Textgenerierung & BYOK API-Key-Management

**Produkt:** QuoteAgent for Shopware 6
**Version:** V1.0
**Priorität:** P1 – Das "Wow-Moment" im Demo
**Entwicklungsphase:** Woche 4

---

## Problem

Der Händler müsste heute den Angebotstext manuell formulieren – professionell, mit den richtigen Zahlen, in passender Tonalität. Das kostet 10–30 Minuten pro Angebot. Das ist der primäre Zeitfresser.

## Ziel

Ein PHP-Service, der fertig kalkulierte Angebotsdaten als strukturierten Prompt an die Claude- oder GPT-API schickt und einen professionellen, editierbaren deutschen Angebotstext zurückerhält. Ohne eigene Infrastruktur (BYOK).

---

## Scope V1.0

### In Scope

- BYOK: Händler hinterlegt eigenen Anthropic- oder OpenAI-API-Key
- Unterstützte Modelle: Claude Sonnet (Anthropic), GPT-4o (OpenAI)
- Prompt-Engineering auf Deutsch mit konfigurierbarer Tonalität
- API-Key Test-Button im Admin (Verbindungstest ohne echten Prompt)
- Fallback: Wenn API-Call fehlschlägt → Angebot mit Platzhalt-Text erstellen (kein Absturz)
- KI-Text im Admin editierbar und neu generierbar

### Out of Scope

- Eigene API-Key-Verwaltung/Abrechnung durch Plugin-Anbieter (V2.0)
- Lokale KI (Ollama/Llama) – Infrastruktur-Overhead (V2.0)
- Mehrsprachige Ausgabe (V2.0)
- RAG/Vektordatenbank für Produktdatenblätter (V2.0)

---

## BYOK-Modell

```
Händler erstellt Account bei Anthropic/OpenAI
        ↓
Händler hinterlegt API-Key im Plugin-Admin
        ↓
Plugin speichert Key verschlüsselt in Shopware SystemConfig
        ↓
Bei jeder Anfrage: PHP-Service macht direkten HTTP-Call
        ↓
API-Kosten gehen auf Händler-Rechnung (< 0,15 €/Monat typisch)
```

**Rechtlich:** Händler hat Vertrag mit Anthropic/OpenAI. Plugin ist technischer Kanal (wie SMTP-Connector). Hinweis in Dokumentation: Produktdaten + Kundendaten können EU verlassen.

---

## Technische Anforderungen

### Service-Signatur

```php
// src/Core/AI/QuoteTextGeneratorService.php

class QuoteTextGeneratorService
{
    public function generate(
        QuoteCalculationResult $calculation,
        QuoteRequestEntity $quoteRequest,
        PluginConfig $config
    ): string; // Gibt den generierten Angebotstext zurück
}
```

### Prompt-Struktur

```
SYSTEM:
Du bist ein professioneller Vertriebsmitarbeiter eines deutschen B2B-Händlers.
Erstelle einen {TONALITÄT} Angebotstext auf Deutsch.
Formuliere höflich, klar und überzeugend.
Nutze ausschließlich die bereitgestellten Zahlen – rechne nichts selbst aus.
{EIGENE_ANWEISUNGEN}

USER:
Kundendaten:
- Name: {KUNDENNAME}, Firma: {FIRMA}
- Anfrage: {KUNDENNACHRICHT}

Kalkulierte Positionen:
{POSITIONEN_JSON}

Gesamtbetrag: {GESAMT_NETTO} € netto / {GESAMT_BRUTTO} € brutto (inkl. {MWST}% MwSt.)
Gültig bis: {GUELTIG_BIS}
Angebotsnummer: {ANGEBOTSNUMMER}

Erstelle einen professionellen Angebotstext (max. 300 Wörter).
Beginne mit einer persönlichen Anrede.
```

### Tonalitäts-Mapping

| Einstellung         | System-Prompt-Ergänzung                          |
|---------------------|--------------------------------------------------|
| `formal`            | "Verwende formelle Sprache (Sie-Form, sachlich)" |
| `friendly`          | "Verwende eine freundlich-lockere Sprache"       |
| `technical`         | "Verwende technisch präzise Fachsprache"         |

### API-Adapter-Implementierung

```php
interface AiProviderInterface
{
    public function complete(string $systemPrompt, string $userPrompt): string;
    public function testConnection(): bool;
}

class AnthropicAdapter implements AiProviderInterface { ... }
class OpenAiAdapter implements AiProviderInterface { ... }
```

**Selektion:** Factory-Pattern basierend auf Plugin-Config (`api_provider`).

### HTTP-Call Parameter

**Claude Sonnet (Anthropic):**
```json
{
  "model": "claude-sonnet-4-6",
  "max_tokens": 1024,
  "messages": [{"role": "user", "content": "..."}],
  "system": "..."
}
```
Header: `x-api-key: {KEY}`, `anthropic-version: 2023-06-01`

**GPT-4o (OpenAI):**
```json
{
  "model": "gpt-4o",
  "max_tokens": 1024,
  "messages": [
    {"role": "system", "content": "..."},
    {"role": "user", "content": "..."}
  ]
}
```
Header: `Authorization: Bearer {KEY}`

### Fehlerbehandlung

| Fehler                  | Verhalten                                                        |
|-------------------------|------------------------------------------------------------------|
| HTTP 401 (Ungültiger Key) | Exception loggen, Admin-Benachrichtigung, Platzhalter-Text      |
| HTTP 429 (Rate Limit)   | Retry nach 60s (1x), dann Platzhalter-Text                      |
| HTTP 5xx (API-Fehler)   | Platzhalter-Text, Angebot trotzdem erstellen                    |
| Timeout (>30s)          | Platzhalter-Text, Angebot trotzdem erstellen                    |

**Platzhalter-Text:**
```
[KI-Textgenerierung fehlgeschlagen. Bitte Text manuell ergänzen.]
Sehr geehrte/r {KUNDENNAME},

vielen Dank für Ihre Anfrage. Anbei erhalten Sie unser Angebot
gemäß Ihrer Anfrage vom {DATUM}.

[Bitte hier weiterschreiben...]
```

### API-Key Speicherung

- Shopware `SystemConfigService`: `QuoteAgent.config.apiKey`
- Anzeige: Masked (nur letzte 4 Zeichen sichtbar)
- Kein Logging des API-Keys (sensible Daten)

---

## Akzeptanzkriterien

- [ ] Test-Button im Admin prüft API-Key-Gültigkeit (kein echter Prompt, nur Auth-Test)
- [ ] Generierter Text erscheint im Admin-Detail-View im editierbaren Textfeld
- [ ] "Neu generieren"-Button erstellt neuen Text (überschreibt vorherigen)
- [ ] Fehlschlag der API führt nicht zu Datenverlust – Angebot wird mit Platzhalter gespeichert
- [ ] API-Key wird nicht in Logs geschrieben
- [ ] Tonalitäts-Einstellung wirkt sich auf generierten Text aus (manuell prüfbar)
- [ ] Eigene Anweisungen (`custom_instructions`) erscheinen im generierten Text

---

## Kosten-Referenz

| Modell        | Input-Kosten   | Typischer Prompt | Kosten/Angebot | 30/Monat |
|---------------|----------------|------------------|----------------|----------|
| Claude Sonnet | $3 / 1M Token  | ~500 Tokens      | ~$0,003        | ~$0,09   |
| GPT-4o        | $5 / 1M Token  | ~500 Tokens      | ~$0,005        | ~$0,15   |

*Für den Händler vernachlässigbar. Kommunikation im Onboarding.*
