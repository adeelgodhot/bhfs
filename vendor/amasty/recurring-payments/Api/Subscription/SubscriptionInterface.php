<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Api\Subscription;

interface SubscriptionInterface
{
    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELED = 'canceled';

    const ID = 'id';
    const CREATED_AT = 'created_at';
    const SUBSCRIPTION_ID = 'subscription_id';
    const ORDER_ID = 'order_id';
    const PRODUCT_ID = 'product_id';
    const QTY = 'qty';
    const CUSTOMER_ID = 'customer_id';
    const PAYMENT_METHOD = 'payment_method';
    const ADDRESS_ID = 'address_id';
    const PRODUCT_OPTIONS = 'product_options';
    const STORE_ID = 'store_id';
    const SHIPPING_METHOD = 'shipping_method';
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
    const INITIAL_FEE = 'initial_fee';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const FREE_SHIPPING = 'free_shipping';
    const TRIAL_DAYS = 'trial_days';
    const DELIVERY = 'delivery';
    const REMAINING_DISCOUNT_CYCLES = 'remaining_discount_cycles';
    const STATUS = 'status';
    const FREQUENCY = 'frequency';
    const FREQUENCY_UNIT = 'frequency_unit';
    const LAST_PAYMENT_DATE = 'last_payment_date';
    const START_DATE = 'start_date';
    const END_DATE = 'end_date';
    const COUNT_DISCOUNT_CYCLES = 'count_discount_cycles';
    const CUSTOMER_TIMEZONE = 'customer_timezone';
    const CUSTOMER_EMAIL = 'customer_email';
    const BASE_GRAND_TOTAL_WITH_DISCOUNT = 'base_grand_total_with_discount';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return SubscriptionInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $dateTime
     *
     * @return SubscriptionInterface
     */
    public function setCreatedAt($dateTime);

    /**
     * @return string
     */
    public function getSubscriptionId();

    /**
     * @param string $subscriptionId
     *
     * @return SubscriptionInterface
     */
    public function setSubscriptionId($subscriptionId);

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $orderId
     *
     * @return SubscriptionInterface
     */
    public function setOrderId($orderId);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $productId
     *
     * @return SubscriptionInterface
     */
    public function setProductId($productId);

    /**
     * @return int
     */
    public function getQty();

    /**
     * @param int $qty
     *
     * @return SubscriptionInterface
     */
    public function setQty($qty);

    /**
     * @return int|null
     */
    public function getCustomerId();

    /**
     * @param int|null $customerId
     *
     * @return SubscriptionInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getPaymentMethod();

    /**
     * @param string $paymentMethod
     *
     * @return SubscriptionInterface
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * @return int|null
     */
    public function getAddressId();

    /**
     * @param int|null $addressId
     *
     * @return SubscriptionInterface
     */
    public function setAddressId($addressId);

    /**
     * @return string
     */
    public function getProductOptions();

    /**
     * @param string $productOptions
     *
     * @return SubscriptionInterface
     */
    public function setProductOptions($productOptions);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     *
     * @return SubscriptionInterface
     */
    public function setStoreId($storeId);

    /**
     * @return string
     */
    public function getShippingMethod();

    /**
     * @param string $shippingMethod
     *
     * @return SubscriptionInterface
     */
    public function setShippingMethod($shippingMethod);

    /**
     * @return float
     */
    public function getInitialFee();

    /**
     * @param float $amount
     *
     * @return SubscriptionInterface
     */
    public function setInitialFee($amount);

    /**
     * @return float
     */
    public function getBaseDiscountAmount();

    /**
     * @param float $baseDiscountAmount
     *
     * @return SubscriptionInterface
     */
    public function setBaseDiscountAmount($baseDiscountAmount);

    /**
     * @return int
     */
    public function getFreeShipping();

    /**
     * @param int $freeShipping
     *
     * @return SubscriptionInterface
     */
    public function setFreeShipping($freeShipping);

    /**
     * @return int
     */
    public function getTrialDays();

    /**
     * @param int $days
     *
     * @return SubscriptionInterface
     */
    public function setTrialDays($days);

    /**
     * @return string
     */
    public function getDelivery();

    /**
     * @param string $delivery
     *
     * @return SubscriptionInterface
     */
    public function setDelivery($delivery);

    /**
     * @return float
     */
    public function getBaseGrandTotal();

    /**
     * @param float $total
     *
     * @return SubscriptionInterface
     */
    public function setBaseGrandTotal($total);

    /**
     * @return int|null
     */
    public function getRemainingDiscountCycles();

    /**
     * @param int $cycles
     *
     * @return SubscriptionInterface
     */
    public function setRemainingDiscountCycles($cycles);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return SubscriptionInterface
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getFrequency();

    /**
     * @param int $interval
     * @return SubscriptionInterface
     */
    public function setFrequency($interval);

    /**
     * @return string
     */
    public function getFrequencyUnit();

    /**
     * @param string $intervalUnit
     * @return SubscriptionInterface
     */
    public function setFrequencyUnit($intervalUnit);

    /**
     * @return string
     */
    public function getLastPaymentDate();

    /**
     * @param string $lastPaymentDate
     * @return SubscriptionInterface
     */
    public function setLastPaymentDate($lastPaymentDate);

    /**
     * @return string
     */
    public function getStartDate();

    /**
     * @param string $startDate
     * @return SubscriptionInterface
     */
    public function setStartDate($startDate);

    /**
     * @return string|null
     */
    public function getEndDate();

    /**
     * @param string $endDate
     * @return SubscriptionInterface
     */
    public function setEndDate($endDate);

    /**
     * @return int
     */
    public function getCountDiscountCycles();

    /**
     * @param int $countDiscountCycles
     * @return SubscriptionInterface
     */
    public function setCountDiscountCycles($countDiscountCycles);

    /**
     * @return string|null
     */
    public function getCustomerTimezone();

    /**
     * @param string $customerTimezone
     * @return SubscriptionInterface
     */
    public function setCustomerTimezone($customerTimezone);

    /**
     * @return string
     */
    public function getCustomerEmail();

    /**
     * @param string $customerEmail
     * @return SubscriptionInterface
     */
    public function setCustomerEmail($customerEmail);

    /**
     * @return float
     */
    public function getBaseGrandTotalWithDiscount();

    /**
     * @param float $baseGrandTotalWithDiscount
     * @return SubscriptionInterface
     */
    public function setBaseGrandTotalWithDiscount($baseGrandTotalWithDiscount);
}
