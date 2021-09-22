<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Api\Data\TransactionInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Transaction as TransactionResource;
use Magento\Framework\Model\AbstractModel;

class Transaction extends AbstractModel implements TransactionInterface
{
    public function _construct()
    {
        $this->_init(TransactionResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getOrderId()
    {
        return $this->_getData(TransactionInterface::ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOrderId($orderId)
    {
        $this->setData(TransactionInterface::ORDER_ID, $orderId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTransactionId()
    {
        return $this->_getData(TransactionInterface::TRANSACTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setTransactionId($transactionId)
    {
        $this->setData(TransactionInterface::TRANSACTION_ID, $transactionId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBillingAmount()
    {
        return $this->_getData(TransactionInterface::BILLING_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setBillingAmount($billingAmount)
    {
        $this->setData(TransactionInterface::BILLING_AMOUNT, $billingAmount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBillingDate()
    {
        return $this->_getData(TransactionInterface::BILLING_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setBillingDate($billingDate)
    {
        $this->setData(TransactionInterface::BILLING_DATE, $billingDate);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(TransactionInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(TransactionInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBillingCurrencyCode()
    {
        return $this->_getData(TransactionInterface::CURRENCY_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setBillingCurrencyCode($currencyCode)
    {
        $this->setData(TransactionInterface::CURRENCY_CODE, $currencyCode);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptionId()
    {
        return $this->_getData(TransactionInterface::SUBSCRIPTION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->setData(TransactionInterface::SUBSCRIPTION_ID, $subscriptionId);

        return $this;
    }
}
