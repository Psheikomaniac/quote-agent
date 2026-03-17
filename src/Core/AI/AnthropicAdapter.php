<?php

declare(strict_types=1);

namespace QuoteAgent\Core\AI;

use Psr\Log\LoggerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Anthropic Claude API adapter (BYOK).
 *
 * @see PRD-004 for full implementation
 */
final class AnthropicAdapter implements AiProviderInterface
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly LoggerInterface $logger,
    ) {}

    public function generateText(string $prompt): string
    {
        // TODO: Implement in PRD-004
        return '';
    }

    public function testConnection(): bool
    {
        // TODO: Implement in PRD-004
        return false;
    }
}
