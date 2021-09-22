<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Subscription as SubscriptionResource;
use Magento\Framework\Model\AbstractModel;

class Subscription extends AbstractModel implements SubscriptionInterface
{
    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(SubscriptionResource::class);
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_getData(SubscriptionInterface::CREATED_AT);
    }

    /**
     * @param string $dateTime
     *
     * @return SubscriptionInterface
     */
    public function setCreatedAt($dateTime)
    {
        $this->setData(SubscriptionInterface::CREATED_AT, $dateTime);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubscriptionId()
    {
        return $this->_getData(SubscriptionInterface::SUBSCRIPTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->setData(SubscriptionInterface::SUBSCRIPTION_ID, $subscriptionId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOrderId()
    {
        return $this->_getData(SubscriptionInterface::ORDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setOrderId($orderId)
    {
        $this->setData(SubscriptionInterface::ORDER_ID, $orderId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->_getData(SubscriptionInterface::PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setProductId($productId)
    {
        $this->setData(SubscriptionInterface::PRODUCT_ID, $productId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQty()
    {
        return $this->_getData(SubscriptionInterface::QTY);
    }

    /**
     * @inheritdoc
     */
    public function setQty($qty)
    {
        $this->setData(SubscriptionInterface::QTY, $qty);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(SubscriptionInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(SubscriptionInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentMethod()
    {
        return $this->_getData(SubscriptionInterface::PAYMENT_METHOD);
    }

    /**
     * @inheritdoc
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->setData(SubscriptionInterface::PAYMENT_METHOD, $paymentMethod);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAddressId()
    {
        return $this->_getData(SubscriptionInterface::ADDRESS_ID);
    }

    /**
     * @inheritdoc
     */
    public function setAddressId($addressId)
    {
        $this->setData(SubscriptionInterface::ADDRESS_ID, $addressId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProductOptions()
    {
        return $this->_getData(SubscriptionInterface::PRODUCT_OPTIONS);
    }

    /**
     * @inheritdoc
     */
    public function setProductOptions($productOptions)
    {
        $this->setData(SubscriptionInterface::PRODUCT_OPTIONS, $productOptions);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->_getData(SubscriptionInterface::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($storeId)
    {
        $this->setData(SubscriptionInterface::STORE_ID, $storeId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShippingMethod()
    {
        return $this->_getData(SubscriptionInterface::SHIPPING_METHOD);
    }

    /**
     * @inheritdoc
     */
    public function setShippingMethod($shippingMethod)
    {
        $this->setData(SubscriptionInterface::SHIPPING_METHOD, $shippingMethod);

        return $this;
    }

    /**
     * @return float
     */
    public function getInitialFee()
    {
        return $this->_getData(SubscriptionInterface::INITIAL_FEE);
    }

    /**
     * @param float $amount
     *
     * @return SubscriptionInterface
     */
    public function setInitialFee($amount)
    {
        $this->setData(SubscriptionInterface::INITIAL_FEE, $amount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBaseDiscountAmount()
    {
        return $this->_getData(SubscriptionInterface::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * @inheritdoc
     */
    public function setBaseDiscountAmount($baseDiscountAmount)
    {
        $this->setData(SubscriptionInterface::BASE_DISCOUNT_AMOUNT, $baseDiscountAmount);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFreeShipping()
    {
        return $this->_getData(SubscriptionInterface::FREE_SHIPPING);
    }

    /**
     * @inheritdoc
     */
    public function setFreeShipping($freeShipping)
    {
        $this->setData(SubscriptionInterface::FREE_SHIPPING, $freeShipping);

        return $this;
    }

    /**
     * @return int
     */
    public function getTrialDays()
    {
        return (int)$this->_getData(SubscriptionInterface::TRIAL_DAYS);
    }

    /**
     * @param int $days
     *
     * @return SubscriptionInterface
     */
    public function setTrialDays($days)
    {
        $this->setData(SubscriptionInterface::TRIAL_DAYS, $days);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDelivery()
    {
        return $this->_getData(SubscriptionInterface::DELIVERY);
    }

    /**
     * @inheritdoc
     */
    public function setDelivery($delivery)
    {
        $this->setData(SubscriptionInterface::DELIVERY, $delivery);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBaseGrandTotal()
    {
        return $this->_getData(SubscriptionInterface::BASE_GRAND_TOTAL);
    }

    /**
     * @inheritdoc
     */
    public function setBaseGrandTotal($total)
    {
        $this->setData(SubscriptionInterface::BASE_GRAND_TOTAL, $total);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRemainingDiscountCycles()
    {
        return $this->_getData(SubscriptionInterface::REMAINING_DISCOUNT_CYCLES);
    }

    /**
     * @param int $cycles
     *
     * @return SubscriptionInterface
     */
    public function setRemainingDiscountCycles($cycles)
    {
        $this->setData(SubscriptionInterface::REMAINING_DISCOUNT_CYCLES, $cycles);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->_getData(SubscriptionInterface::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($status)
    {
        $this->setData(SubscriptionInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFrequency()
    {
        return (int)$this->_getData(SubscriptionInterface::FREQUENCY);
    }

    /**
     * @inheritDoc
     */
    public function setFrequency($interval)
    {
        $this->setData(SubscriptionInterface::FREQUENCY, $interval);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFrequencyUnit()
    {
        return (string)$this->_getData(SubscriptionInterface::FREQUENCY_UNIT);
    }

    /**
     * @inheritDoc
     */
    public function setFrequencyUnit($intervalUnit)
    {
        $this->setData(SubscriptionInterface::FREQUENCY_UNIT, $intervalUnit);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLastPaymentDate()
    {
        return $this->_getData(SubscriptionInterface::LAST_PAYMENT_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setLastPaymentDate($lastPaymentDate)
    {
        $this->setData(SubscriptionInterface::LAST_PAYMENT_DATE, $lastPaymentDate);

        return $this;
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->_getData(SubscriptionInterface::START_DATE);
    }

    /**
     * @param string $startDate
     * @return $this
     */
    public function setStartDate($startDate)
    {
        $this->setData(SubscriptionInterface::START_DATE, $startDate);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEndDate()
    {
        return $this->_getData(SubscriptionInterface::END_DATE);
    }

    /**
     * @param string $endDate
     * @return $this
     */
    public function setEndDate($endDate)
    {
        $this->setData(SubscriptionInterface::END_DATE, $endDate);

        return $this;
    }

    /**
     * @return int
     */
    public function getCountDiscountCycles()
    {
        return (int)$this->_getData(SubscriptionInterface::COUNT_DISCOUNT_CYCLES);
    }

    /**
     * @param int $countDiscountCycles
     * @return $this
     */
    public function setCountDiscountCycles($countDiscountCycles)
    {
        $this->setData(SubscriptionInterface::COUNT_DISCOUNT_CYCLES, $countDiscountCycles);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomerTimezone()
    {
        return $this->getData(SubscriptionInterface::CUSTOMER_TIMEZONE);
    }

    /**
     * @param string $customerTimezone
     * @return $this
     */
    public function setCustomerTimezone($customerTimezone)
    {
        $this->setData(SubscriptionInterface::CUSTOMER_TIMEZONE, $customerTimezone);

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomerEmail(): string
    {
        return (string) $this->getData(SubscriptionInterface::CUSTOMER_EMAIL);
    }

    /**
     * @param string $customerEmail
     * @return $this
     */
    public function setCustomerEmail($customerEmail)
    {
        $this->setData(SubscriptionInterface::CUSTOMER_EMAIL, $customerEmail);

        return $this;
    }

    /**
     * @return float
     */
    public function getBaseGrandTotalWithDiscount()
    {
        return $this->getData(SubscriptionInterface::BASE_GRAND_TOTAL_WITH_DISCOUNT);
    }

    /**
     * @param float $baseGrandTotalWithDiscount
     * @return $this
     */
    public function setBaseGrandTotalWithDiscount($baseGrandTotalWithDiscount)
    {
        $this->setData(SubscriptionInterface::BASE_GRAND_TOTAL_WITH_DISCOUNT, $baseGrandTotalWithDiscount);

        return $this;
    }
}
