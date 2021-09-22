<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Model\ResourceModel\Fee as FeeResource;
use Amasty\RecurringPayments\Api\Data\FeeInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Fee
 */
class Fee extends AbstractModel implements FeeInterface
{
    public function _construct()
    {
        $this->_init(FeeResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->_getData('entity_id');
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId): FeeInterface
    {
        return $this->setData('entity_id', $entityId);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteId(): int
    {
        return (int)$this->_getData(FeeInterface::QUOTE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setQuoteId(int $quoteId): FeeInterface
    {
        $this->setData(FeeInterface::QUOTE_ID, $quoteId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAmount(): float
    {
        return (float)$this->_getData(FeeInterface::AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setAmount(float $amount): FeeInterface
    {
        $this->setData(FeeInterface::AMOUNT, $amount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBaseAmount(): float
    {
        return (float)$this->_getData(FeeInterface::BASE_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setBaseAmount(float $baseAmount): FeeInterface
    {
        $this->setData(FeeInterface::BASE_AMOUNT, $baseAmount);

        return $this;
    }
}
