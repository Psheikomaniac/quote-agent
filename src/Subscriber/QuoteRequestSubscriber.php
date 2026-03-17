<?php

declare(strict_types=1);

namespace QuoteAgent\Subscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Reacts to domain events in the quote lifecycle.
 *
 * @see PRD-006 (Email) and PRD-008 (Status Tracking) for full implementation
 */
final class QuoteRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [];
    }
}
