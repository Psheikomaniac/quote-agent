<?php

declare(strict_types=1);

namespace QuoteAgent\ScheduledTask;

use Psr\Log\LoggerInterface;
use QuoteAgent\Core\Quote\QuoteRequestRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;

/**
 * Handles the QuoteExpirationTask: transitions all 'sent' quotes past their
 * valid_until date to status 'expired'.
 *
 * @see PRD-008 for full implementation
 */
final class QuoteExpirationTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        private readonly LoggerInterface $logger,
        private readonly QuoteRequestRepository $quoteRequestRepository,
    ) {
        parent::__construct($scheduledTaskRepository);
    }

    public static function getHandledMessages(): iterable
    {
        return [QuoteExpirationTask::class];
    }

    public function run(): void
    {
        // TODO: Implement in PRD-008 – query b2b_quote_request WHERE status='sent'
        // AND valid_until < NOW(), update to status='expired'
    }
}
