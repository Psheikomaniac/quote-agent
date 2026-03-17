<?php

declare(strict_types=1);

namespace QuoteAgent\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

/**
 * Scheduled task that runs hourly to mark overdue quotes as 'expired'.
 *
 * @see PRD-008 for full implementation
 */
final class QuoteExpirationTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'quote_agent.quote_expiration';
    }

    public static function getDefaultInterval(): int
    {
        return 3600; // 1 hour
    }
}
