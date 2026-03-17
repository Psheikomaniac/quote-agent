<?php

declare(strict_types=1);

namespace QuoteAgent\Tests\Unit\Core\Quote;

use PHPUnit\Framework\TestCase;
use QuoteAgent\Core\Quote\QuoteRequestEntity;

final class QuoteRequestEntityTest extends TestCase
{
    public function testDefaultStatus(): void
    {
        $entity = new QuoteRequestEntity();

        $this->assertSame('new', $entity->getStatus());
    }

    public function testOptionalFieldsAreNullByDefault(): void
    {
        $entity = new QuoteRequestEntity();

        $this->assertNull($entity->getItems());
        $this->assertNull($entity->getCustomerMessage());
        $this->assertNull($entity->getAiText());
        $this->assertNull($entity->getTotalAmount());
        $this->assertNull($entity->getValidUntil());
        $this->assertNull($entity->getPdfPath());
        $this->assertNull($entity->getCustomer());
    }

    public function testGettersAndSetters(): void
    {
        $entity = new QuoteRequestEntity();

        // TODO: Implement this test
        // Set all fields using setters and verify via getters.
        // Cover both non-null and null cases for optional fields.
        // Hint: use \DateTime for validUntil, array for items.
        $this->markTestIncomplete('Please implement testGettersAndSetters()');
    }
}
