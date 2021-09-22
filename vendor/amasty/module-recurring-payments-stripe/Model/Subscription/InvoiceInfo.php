<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\Subscription;

use Magento\Framework\DataObject;

class InvoiceInfo extends DataObject
{
    public function getId(): string
    {
        return $this->getData('id');
    }

    public function setId(string $id): self
    {
        return $this->setData('id', $id);
    }

    public function getDate(): int
    {
        return $this->getData('date');
    }

    public function setDate(int $date): self
    {
        return $this->setData('date', $date);
    }

    public function getAmount(): float
    {
        return $this->getData('amount');
    }

    public function setAmount(float $amount): self
    {
        return $this->setData('amount', $amount);
    }

    public function getCurrency(): string
    {
        return $this->getData('currency');
    }

    public function setCurrency(string $currency): self
    {
        return $this->setData('currency', $currency);
    }
}
