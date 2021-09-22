<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Api\Data\DiscountInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Discount
 */
class Discount extends AbstractModel implements DiscountInterface
{
    public function _construct()
    {
        $this->_init(ResourceModel\Discount::class);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->_getData(DiscountInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(DiscountInterface::ENTITY_ID, $entityId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubscriptionId()
    {
        return $this->_getData(DiscountInterface::SUBSCRIPTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->setData(DiscountInterface::SUBSCRIPTION_ID, $subscriptionId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDiscountUsage()
    {
        return (int)$this->_getData(DiscountInterface::DISCOUNT_USAGE);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountUsage($discountUsage)
    {
        $this->setData(DiscountInterface::DISCOUNT_USAGE, $discountUsage);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAvailableDiscountUsage()
    {
        return (int)$this->_getData(DiscountInterface::AVAILABLE_DISCOUNT_USAGE);
    }

    /**
     * @inheritdoc
     */
    public function setAvailableDiscountUsage($availableDiscountUsage)
    {
        $this->setData(DiscountInterface::AVAILABLE_DISCOUNT_USAGE, $availableDiscountUsage);

        return $this;
    }
}
