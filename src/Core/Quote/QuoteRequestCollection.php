<?php

declare(strict_types=1);

namespace QuoteAgent\Core\Quote;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<QuoteRequestEntity>
 */
class QuoteRequestCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return QuoteRequestEntity::class;
    }
}
