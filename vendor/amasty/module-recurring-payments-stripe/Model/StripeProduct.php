<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model;

use Amasty\RecurringStripe\Api\Data\ProductInterface;
use Amasty\RecurringStripe\Model\ResourceModel\StripeProduct as StripeProductResource;
use Magento\Framework\Model\AbstractModel;

class StripeProduct extends AbstractModel implements ProductInterface
{
    public function _construct()
    {
        $this->_init(StripeProductResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->_getData(ProductInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(ProductInterface::ENTITY_ID, $entityId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->_getData(ProductInterface::PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setProductId($productId)
    {
        $this->setData(ProductInterface::PRODUCT_ID, $productId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStripeProductId()
    {
        return $this->_getData(ProductInterface::STRIPE_PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStripeProductId($stripeProductId)
    {
        $this->setData(ProductInterface::STRIPE_PRODUCT_ID, $stripeProductId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStripeAccountId()
    {
        return $this->_getData(ProductInterface::STRIPE_ACCOUNT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStripeAccountId($stripeAccountId)
    {
        $this->setData(ProductInterface::STRIPE_ACCOUNT_ID, $stripeAccountId);

        return $this;
    }
}
