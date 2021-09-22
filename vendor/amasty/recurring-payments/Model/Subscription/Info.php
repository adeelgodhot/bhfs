<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription;

use Amasty\RecurringPayments\Api\Subscription\AddressInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInfoInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Magento\Framework\DataObject;

/**
 * Class Info
 * Contains subscription data to pass to front-end
 */
class Info extends DataObject implements SubscriptionInfoInterface
{
    public function jsonSerialize()
    {
        $data = [];
        foreach ($this->_data as $key => $value) {
            if ($value instanceof DataObject) {
                $data[$key] = $value->getData();
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**#@-
     * @param SubscriptionInterface $subscription
     * @return SubscriptionInfoInterface
     */
    public function setSubscription(SubscriptionInterface $subscription): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::SUBSCRIPTION, $subscription);

        return $this;
    }

    public function getSubscription(): SubscriptionInterface
    {
        return $this->getData(SubscriptionInfoInterface::SUBSCRIPTION);
    }

    /**
     * @param AddressInterface $address
     * @return $this
     */
    public function setAddress(AddressInterface $address): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::ADDRESS, $address);

        return $this;
    }

    /**
     * @param string $increment
     * @return $this
     */
    public function setOrderIncrementId(string $increment): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::ORDER_INCREMENT_ID, $increment);

        return $this;
    }

    /**
     * @param string $link
     * @return $this
     */
    public function setOrderLink(string $link): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::ORDER_LINK, $link);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setSubscriptionName(string $name): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::SUBSCRIPTION_NAME, $name);

        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setStartDate(string $date): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::START_DATE, $date);

        return $this;
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->getData(SubscriptionInfoInterface::START_DATE);
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setTrialStartDate(string $date): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::TRIAL_START_DATE, $date);

        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setTrialEndDate(string $date): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::TRIAL_END_DATE, $date);

        return $this;
    }

    /**
     * @param string $delivery
     * @return $this
     */
    public function setDelivery(string $delivery): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::DELIVERY, $delivery);

        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setLastBilling(string $date): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::LAST_BILLING, $date);

        return $this;
    }

    /**
     * @return string
     */
    public function getLastBilling(): string
    {
        return (string)$this->getData(SubscriptionInfoInterface::LAST_BILLING);
    }

    /**
     * @param string $amount
     * @return $this
     */
    public function setLastBillingAmount(string $amount): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::LAST_BILLING_AMOUNT, $amount);

        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setNextBilling(string $date): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::NEXT_BILLING, $date);

        return $this;
    }

    public function getNextBilling(): string
    {
        return (string)$this->getData(SubscriptionInfoInterface::NEXT_BILLING);
    }

    /**
     * @param string $amount
     * @return $this
     */
    public function setNextBillingAmount(string $amount): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::NEXT_BILLING_AMOUNT, $amount);

        return $this;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::STATUS, $status);

        return $this;
    }

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::IS_ACTIVE, $isActive);

        return $this;
    }

    /**
     * @param string $link
     * @return $this
     */
    public function setApprovalLink(string $link): SubscriptionInfoInterface
    {
        $this->setData(SubscriptionInfoInterface::APPROVAL_LINK, $link);

        return $this;
    }
}
