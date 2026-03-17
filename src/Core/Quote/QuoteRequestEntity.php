<?php

declare(strict_types=1);

namespace QuoteAgent\Core\Quote;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Checkout\Customer\CustomerEntity;

class QuoteRequestEntity extends Entity
{
    use EntityIdTrait;

    protected string $quoteNumber = '';

    protected string $customerId = '';

    protected string $status = 'new';

    protected ?array $items = null;

    protected ?string $customerMessage = null;

    protected ?string $aiText = null;

    protected ?float $totalAmount = null;

    protected ?\DateTimeInterface $validUntil = null;

    protected string $acceptToken = '';

    protected ?string $pdfPath = null;

    protected ?CustomerEntity $customer = null;

    public function getQuoteNumber(): string
    {
        return $this->quoteNumber;
    }

    public function setQuoteNumber(string $quoteNumber): void
    {
        $this->quoteNumber = $quoteNumber;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getItems(): ?array
    {
        return $this->items;
    }

    public function setItems(?array $items): void
    {
        $this->items = $items;
    }

    public function getCustomerMessage(): ?string
    {
        return $this->customerMessage;
    }

    public function setCustomerMessage(?string $customerMessage): void
    {
        $this->customerMessage = $customerMessage;
    }

    public function getAiText(): ?string
    {
        return $this->aiText;
    }

    public function setAiText(?string $aiText): void
    {
        $this->aiText = $aiText;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(?float $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    public function getValidUntil(): ?\DateTimeInterface
    {
        return $this->validUntil;
    }

    public function setValidUntil(?\DateTimeInterface $validUntil): void
    {
        $this->validUntil = $validUntil;
    }

    public function getAcceptToken(): string
    {
        return $this->acceptToken;
    }

    public function setAcceptToken(string $acceptToken): void
    {
        $this->acceptToken = $acceptToken;
    }

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function setPdfPath(?string $pdfPath): void
    {
        $this->pdfPath = $pdfPath;
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }
}
