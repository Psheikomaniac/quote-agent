<?php

declare(strict_types=1);

namespace QuoteAgent\Core\AI;

use Psr\Log\LoggerInterface;

/**
 * Orchestrates AI text generation for quotes.
 * Receives pre-calculated pricing data from QuotePricingService and delegates
 * text generation to the configured AI provider adapter.
 *
 * @see PRD-004 for full implementation
 */
final class QuoteTextGeneratorService
{
    public function __construct(
        private readonly AiProviderInterface $aiProvider,
        private readonly LoggerInterface $logger,
    ) {}
}
