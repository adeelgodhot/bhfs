<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model;

use Amasty\RecurringPaypal\Api\Data\ProductInterface;
use Amasty\RecurringPaypal\Model\ResourceModel\PaypalProduct as PaypalProductResource;
use Magento\Framework\Model\AbstractModel;

class PaypalProduct extends AbstractModel implements ProductInterface
{
    public function _construct()
    {
        $this->_init(PaypalProductResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->_getData(ProductInterface::ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entityId)
    {
        $this->setData(ProductInterface::ID, $entityId);

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
    public function getPaypalProductId()
    {
        return $this->_getData(ProductInterface::PAYPAL_PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setPaypalProductId($paypalProductId)
    {
        $this->setData(ProductInterface::PAYPAL_PRODUCT_ID, $paypalProductId);

        return $this;
    }
}
