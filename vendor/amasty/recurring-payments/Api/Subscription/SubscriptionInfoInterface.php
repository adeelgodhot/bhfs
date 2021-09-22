<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Subscription;

interface SubscriptionInfoInterface extends \JsonSerializable
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const SUBSCRIPTION = 'subscription';
    const ADDRESS = 'address';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const ORDER_LINK = 'order_link';
    const SUBSCRIPTION_NAME = 'subscription_name';
    const START_DATE = 'start_date';
    const QUANTITY = 'quantity';
    const TRIAL_START_DATE = 'trial_start_date';
    const TRIAL_END_DATE = 'trial_end_date';
    const DELIVERY = 'delivery';
    const LAST_BILLING = 'last_billing';
    const LAST_BILLING_AMOUNT = 'last_billing_amount';
    const NEXT_BILLING = 'next_billing';
    const NEXT_BILLING_AMOUNT = 'next_billing_amount';
    const STATUS = 'status';
    const IS_ACTIVE = 'is_active';
    const APPROVAL_LINK = 'approval_link';

    /**#@-
     * @param SubscriptionInterface $subscription
     * @return SubscriptionInfoInterface
     */
    public function setSubscription(SubscriptionInterface $subscription): self;

    public function getSubscription(): SubscriptionInterface;

    /**
     * @param AddressInterface $address
     * @return $this
     */
    public function setAddress(AddressInterface $address): self;

    /**
     * @param string $increment
     * @return $this
     */
    public function setOrderIncrementId(string $increment): self;

    /**
     * @param string $link
     * @return $this
     */
    public function setOrderLink(string $link): self;

    /**
     * @param string $name
     * @return $this
     */
    public function setSubscriptionName(string $name): self;

    /**
     * @param string $date
     * @return $this
     */
    public function setStartDate(string $date): self;

    /**
     * @return string
     */
    public function getStartDate(): string;

    /**
     * @param string $date
     * @return $this
     */
    public function setTrialStartDate(string $date): self;

    /**
     * @param string $date
     * @return $this
     */
    public function setTrialEndDate(string $date): self;

    /**
     * @param string $delivery
     * @return $this
     */
    public function setDelivery(string $delivery): self;

    /**
     * @param string $date
     * @return $this
     */
    public function setLastBilling(string $date): self;

    /**
     * @return string
     */
    public function getLastBilling(): string;

    /**
     * @param string $amount
     * @return $this
     */
    public function setLastBillingAmount(string $amount): self;

    /**
     * @param string $date
     * @return $this
     */
    public function setNextBilling(string $date): self;

    /**
     * @return string
     */
    public function getNextBilling(): string;

    /**
     * @param string $amount
     * @return $this
     */
    public function setNextBillingAmount(string $amount): self;

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus(string $status): self;

    /**
     * @param string $link
     * @return $this
     */
    public function setApprovalLink(string $link): self;

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive(bool $isActive): self;
}
