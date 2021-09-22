<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\Estimation;

use Amasty\RecurringPayments\Api\Data\EstimationItemInterface;
use Magento\Framework\DataObject;

class EstimationItem extends DataObject implements EstimationItemInterface
{
    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->getData(self::VALUE);
    }

    /**
     * @param float $value
     * @return void
     */
    public function setValue(float $value): void
    {
        $this->setData(self::VALUE, $value);
    }

    /**
     * @return int
     */
    public function getItemId(): int
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * @param int $itemId
     * @return void
     */
    public function setItemId(int $itemId): void
    {
        $this->setData(self::ITEM_ID, $itemId);
    }
}
