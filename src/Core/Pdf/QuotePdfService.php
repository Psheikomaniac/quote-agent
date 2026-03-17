<?php

declare(strict_types=1);

namespace QuoteAgent\Core\Pdf;

use Psr\Log\LoggerInterface;

/**
 * Generates PDF documents for quotes using DomPDF.
 * DomPDF is always configured with isRemoteEnabled=false (no external HTTP).
 * PDFs are only served via authenticated admin endpoint.
 *
 * @see PRD-005 for full implementation
 */
final class QuotePdfService
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}
}
