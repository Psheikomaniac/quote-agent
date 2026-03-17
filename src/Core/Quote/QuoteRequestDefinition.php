<?php

declare(strict_types=1);

namespace QuoteAgent\Core\Quote;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Checkout\Customer\CustomerDefinition;

final class QuoteRequestDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'b2b_quote_request';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return QuoteRequestEntity::class;
    }

    public function getCollectionClass(): string
    {
        return QuoteRequestCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('quote_number', 'quoteNumber'))->addFlags(new Required()),
            (new FkField('customer_id', 'customerId', CustomerDefinition::class))->addFlags(new Required()),
            (new StringField('status', 'status'))->addFlags(new Required()),
            new JsonField('items', 'items'),
            new LongTextField('customer_message', 'customerMessage'),
            new LongTextField('ai_text', 'aiText'),
            new FloatField('total_amount', 'totalAmount'),
            new DateTimeField('valid_until', 'validUntil'),
            (new StringField('accept_token', 'acceptToken'))->addFlags(new Required()),
            new StringField('pdf_path', 'pdfPath'),
            new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class),
        ]);
    }
}
