<?php

declare(strict_types=1);

namespace QuoteAgent\Core\Pricing;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Calculates all pricing data for a quote request using Shopware DAL.
 * Resolves tier prices, customer-group conditions, and checks minimum margins.
 * The AI never receives raw product data – only the finished calculation result.
 *
 * @see PRD-003 for full implementation
 */
final class QuotePricingService
{
    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly LoggerInterface $logger,
    ) {}
}
