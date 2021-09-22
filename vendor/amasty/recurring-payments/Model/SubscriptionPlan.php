<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan as SubscriptionPlanResource;
use Magento\Framework\Model\AbstractModel;

class SubscriptionPlan extends AbstractModel implements SubscriptionPlanInterface
{
    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(SubscriptionPlanResource::class);
    }

    /**
     * @return int|null
     */
    public function getPlanId()
    {
        return $this->_getData(SubscriptionPlanInterface::PLAN_ID);
    }

    /**
     * @param int|null $planId
     * @return SubscriptionPlanInterface
     */
    public function setPlanId($planId)
    {
        $this->setData(SubscriptionPlanInterface::PLAN_ID, $planId);

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_getData(SubscriptionPlanInterface::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return SubscriptionPlanInterface
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(SubscriptionPlanInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_getData(SubscriptionPlanInterface::NAME);
    }

    /**
     * @param string $name
     * @return SubscriptionPlanInterface
     */
    public function setName($name)
    {
        $this->setData(SubscriptionPlanInterface::NAME, $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->_getData(SubscriptionPlanInterface::STATUS);
    }

    /**
     * @param string $status
     * @return SubscriptionPlanInterface
     */
    public function setStatus($status)
    {
        $this->setData(SubscriptionPlanInterface::STATUS, $status);

        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency(): int
    {
        return (int)$this->_getData(SubscriptionPlanInterface::FREQUENCY);
    }

    /**
     * @param int $frequency
     * @return SubscriptionPlanInterface
     */
    public function setFrequency(int $frequency)
    {
        $this->setData(SubscriptionPlanInterface::FREQUENCY, $frequency);

        return $this;
    }

    /**
     * @return string
     */
    public function getFrequencyUnit(): string
    {
        return (string)$this->_getData(SubscriptionPlanInterface::FREQUENCY_UNIT);
    }

    /**
     * @param string $frequencyUnit
     * @return SubscriptionPlanInterface
     */
    public function setFrequencyUnit(string $frequencyUnit)
    {
        $this->setData(SubscriptionPlanInterface::FREQUENCY_UNIT, $frequencyUnit);

        return $this;
    }

    /**
     * @return int
     */
    public function getEnableTrial()
    {
        return $this->_getData(SubscriptionPlanInterface::ENABLE_TRIAL);
    }

    /**
     * @param int $enableTrial
     * @return SubscriptionPlanInterface
     */
    public function setEnableTrial($enableTrial)
    {
        $this->setData(SubscriptionPlanInterface::ENABLE_TRIAL, $enableTrial);

        return $this;
    }

    /**
     * @return int
     */
    public function getTrialDays(): int
    {
        return (int)$this->_getData(SubscriptionPlanInterface::TRIAL_DAYS);
    }

    /**
     * @param int $trialDays
     * @return SubscriptionPlanInterface
     */
    public function setTrialDays(int $trialDays)
    {
        $this->setData(SubscriptionPlanInterface::TRIAL_DAYS, $trialDays);

        return $this;
    }

    /**
     * @return int
     */
    public function getEnableInitialFee()
    {
        return $this->_getData(SubscriptionPlanInterface::ENABLE_INITIAL_FEE);
    }

    /**
     * @param int $enableInitialFee
     * @return SubscriptionPlanInterface
     */
    public function setEnableInitialFee($enableInitialFee)
    {
        $this->setData(SubscriptionPlanInterface::ENABLE_INITIAL_FEE, $enableInitialFee);

        return $this;
    }

    /**
     * @return string
     */
    public function getInitialFeeType(): string
    {
        return (string)$this->_getData(SubscriptionPlanInterface::INITIAL_FEE_TYPE);
    }

    /**
     * @param string $initialFeeType
     * @return SubscriptionPlanInterface
     */
    public function setInitialFeeType($initialFeeType)
    {
        $this->setData(SubscriptionPlanInterface::INITIAL_FEE_TYPE, $initialFeeType);

        return $this;
    }

    /**
     * @return float
     */
    public function getInitialFeeAmount()
    {
        return $this->_getData(SubscriptionPlanInterface::INITIAL_FEE_AMOUNT);
    }

    /**
     * @param float $initialFeeAmount
     * @return SubscriptionPlanInterface
     */
    public function setInitialFeeAmount($initialFeeAmount)
    {
        $this->setData(SubscriptionPlanInterface::INITIAL_FEE_AMOUNT, $initialFeeAmount);

        return $this;
    }

    /**
     * @return int
     */
    public function getEnableDiscount()
    {
        return $this->_getData(SubscriptionPlanInterface::ENABLE_DISCOUNT);
    }

    /**
     * @param int $enableDiscount
     * @return SubscriptionPlanInterface
     */
    public function setEnableDiscount($enableDiscount)
    {
        $this->setData(SubscriptionPlanInterface::ENABLE_DISCOUNT, $enableDiscount);

        return $this;
    }

    /**
     * @return string
     */
    public function getDiscountType(): string
    {
        return (string)$this->_getData(SubscriptionPlanInterface::DISCOUNT_TYPE);
    }

    /**
     * @param string $discountType
     * @return SubscriptionPlanInterface
     */
    public function setDiscountType($discountType)
    {
        $this->setData(SubscriptionPlanInterface::DISCOUNT_TYPE, $discountType);

        return $this;
    }

    /**
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->_getData(SubscriptionPlanInterface::DISCOUNT_AMOUNT);
    }

    /**
     * @param float $discountAmount
     * @return SubscriptionPlanInterface
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->setData(SubscriptionPlanInterface::DISCOUNT_AMOUNT, $discountAmount);

        return $this;
    }

    /**
     * @return int
     */
    public function getEnableDiscountLimit()
    {
        return $this->_getData(SubscriptionPlanInterface::ENABLE_DISCOUNT_LIMIT);
    }

    /**
     * @param int $enableDiscountLimit
     * @return SubscriptionPlanInterface
     */
    public function setEnableDiscountLimit($enableDiscountLimit)
    {
        $this->setData(SubscriptionPlanInterface::ENABLE_DISCOUNT_LIMIT, $enableDiscountLimit);

        return $this;
    }

    /**
     * @return int
     */
    public function getNumberOfDiscountCycles(): int
    {
        return (int)$this->_getData(SubscriptionPlanInterface::NUMBER_DISCOUNT_CYCLES);
    }

    /**
     * @param int $numberOfDiscountCycles
     * @return SubscriptionPlanInterface
     */
    public function setNumberOfDiscountCycles(int $numberOfDiscountCycles)
    {
        $this->setData(SubscriptionPlanInterface::NUMBER_DISCOUNT_CYCLES, $numberOfDiscountCycles);

        return $this;
    }
}
