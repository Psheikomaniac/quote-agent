<?php

declare(strict_types=1);

namespace QuoteAgent\Core\AI;

interface AiProviderInterface
{
    /**
     * Generates text based on the given prompt.
     * The prompt must contain pre-calculated numbers – the AI never computes values itself.
     */
    public function generateText(string $prompt): string;

    /**
     * Tests whether the provider is reachable with the configured API key.
     */
    public function testConnection(): bool;
}
