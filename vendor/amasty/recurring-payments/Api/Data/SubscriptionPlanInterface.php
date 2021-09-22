<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Data;

interface SubscriptionPlanInterface
{
    const PLAN_ID = 'plan_id';
    const CREATED_AT = 'created_at';
    const NAME = 'name';
    const STATUS = 'status';
    const FREQUENCY = 'frequency';
    const FREQUENCY_UNIT = 'frequency_unit';
    const ENABLE_TRIAL = 'enable_trial';
    const TRIAL_DAYS = 'trial_days';
    const ENABLE_INITIAL_FEE = 'enable_initial_fee';
    const INITIAL_FEE_TYPE = 'initial_fee_type';
    const INITIAL_FEE_AMOUNT = 'initial_fee_amount';
    const ENABLE_DISCOUNT = 'enable_discount';
    const DISCOUNT_TYPE = 'discount_type';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const ENABLE_DISCOUNT_LIMIT = 'enable_discount_limit';
    const NUMBER_DISCOUNT_CYCLES = 'number_discounted_cycles';

    /**
     * @return int|null
     */
    public function getPlanId();

    /**
     * @param int|null $planId
     * @return SubscriptionPlanInterface
     */
    public function setPlanId($planId);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return SubscriptionPlanInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return SubscriptionPlanInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return SubscriptionPlanInterface
     */
    public function setStatus($status);

    /**
     * @return int
     */
    public function getFrequency(): int;

    /**
     * @param int $frequency
     * @return SubscriptionPlanInterface
     */
    public function setFrequency(int $frequency);

    /**
     * @return string
     */
    public function getFrequencyUnit(): string;

    /**
     * @param string $frequencyUnit
     * @return SubscriptionPlanInterface
     */
    public function setFrequencyUnit(string $frequencyUnit);

    /**
     * @return int
     */
    public function getEnableTrial();

    /**
     * @param int $enableTrial
     * @return SubscriptionPlanInterface
     */
    public function setEnableTrial($enableTrial);

    /**
     * @return int
     */
    public function getTrialDays(): int;

    /**
     * @param int $trialDays
     * @return SubscriptionPlanInterface
     */
    public function setTrialDays(int $trialDays);

    /**
     * @return int
     */
    public function getEnableInitialFee();

    /**
     * @param int $enableInitialFee
     * @return SubscriptionPlanInterface
     */
    public function setEnableInitialFee($enableInitialFee);

    /**
     * @return string
     */
    public function getInitialFeeType(): string;

    /**
     * @param string $initialFeeType
     * @return SubscriptionPlanInterface
     */
    public function setInitialFeeType($initialFeeType);

    /**
     * @return float
     */
    public function getInitialFeeAmount();

    /**
     * @param float $initialFeeAmount
     * @return SubscriptionPlanInterface
     */
    public function setInitialFeeAmount($initialFeeAmount);

    /**
     * @return int
     */
    public function getEnableDiscount();

    /**
     * @param int $enableDiscount
     * @return SubscriptionPlanInterface
     */
    public function setEnableDiscount($enableDiscount);

    /**
     * @return string
     */
    public function getDiscountType(): string;

    /**
     * @param string $discountType
     * @return SubscriptionPlanInterface
     */
    public function setDiscountType($discountType);

    /**
     * @return float
     */
    public function getDiscountAmount();

    /**
     * @param float $discountAmount
     * @return SubscriptionPlanInterface
     */
    public function setDiscountAmount($discountAmount);

    /**
     * @return int
     */
    public function getEnableDiscountLimit();

    /**
     * @param int $enableDiscountLimit
     * @return SubscriptionPlanInterface
     */
    public function setEnableDiscountLimit($enableDiscountLimit);

    /**
     * @return int
     */
    public function getNumberOfDiscountCycles(): int;

    /**
     * @param int $numberOfDiscountCycles
     * @return SubscriptionPlanInterface
     */
    public function setNumberOfDiscountCycles(int $numberOfDiscountCycles);
}
