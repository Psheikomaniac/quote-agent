<?php

declare(strict_types=1);

namespace QuoteAgent\Core\Quote;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

/**
 * Typed wrapper around the auto-generated b2b_quote_request.repository service.
 * Injecting this class instead of EntityRepository directly improves readability
 * and enables precise mocking in unit tests.
 */
final class QuoteRequestRepository
{
    public function __construct(
        private readonly EntityRepository $quoteRequestRepository,
    ) {}

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        return $this->quoteRequestRepository->search($criteria, $context);
    }

    public function create(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->quoteRequestRepository->create($data, $context);
    }

    public function update(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->quoteRequestRepository->update($data, $context);
    }

    public function delete(array $ids, Context $context): EntityWrittenContainerEvent
    {
        return $this->quoteRequestRepository->delete($ids, $context);
    }
}
