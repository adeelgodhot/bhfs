<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Data;

/**
 * Interface TransactionInterface
 */
interface TransactionInterface
{
    const STATUS_FAIL = 0;
    const STATUS_SUCCESS = 1;
    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const TRANSACTION_ID = 'transaction_id';
    const ORDER_ID = 'order_id';
    const BILLING_AMOUNT = 'billing_amount';
    const BILLING_DATE = 'billing_date';
    const STATUS = 'status';
    const CURRENCY_CODE = 'billing_currency code';
    const SUBSCRIPTION_ID = 'subscription_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return \Amasty\RecurringPayments\Api\Data\TransactionInterface
     */
    public function setEntityId($entityId);

    /**
     * @return string
     */
    public function getTransactionId();

    /**
     * @param string $transactionId
     *
     * @return \Amasty\RecurringPayments\Api\Data\TransactionInterface
     */
    public function setTransactionId($transactionId);

    /**
     * @return string|null
     */
    public function getOrderId();

    /**
     * @param string|null $orderId
     *
     * @return \Amasty\RecurringPayments\Api\Data\TransactionInterface
     */
    public function setOrderId($orderId);

    /**
     * @return float
     */
    public function getBillingAmount();

    /**
     * @param float $billingAmount
     *
     * @return \Amasty\RecurringPayments\Api\Data\TransactionInterface
     */
    public function setBillingAmount($billingAmount);

    /**
     * @return string
     */
    public function getBillingDate();

    /**
     * @param string $billingDate
     *
     * @return \Amasty\RecurringPayments\Api\Data\TransactionInterface
     */
    public function setBillingDate($billingDate);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $status
     *
     * @return \Amasty\RecurringPayments\Api\Data\TransactionInterface
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getBillingCurrencyCode();

    /**
     * @param string $currencyCode
     *
     * @return \Amasty\RecurringPayments\Api\Data\TransactionInterface
     */
    public function setBillingCurrencyCode($currencyCode);

    /**
     * @return string
     */
    public function getSubscriptionId();

    /**
     * @param string $subscriptionId
     *
     * @return \Amasty\RecurringPayments\Api\Data\TransactionInterface
     */
    public function setSubscriptionId($subscriptionId);
}
