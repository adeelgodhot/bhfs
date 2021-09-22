<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Quote\Validator;

use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Product;
use Amasty\RecurringPayments\Model\Quote\ItemDataRetriever;
use Amasty\RecurringPayments\Model\Subscription\Mapper\StartEndDateMapper;
use Amasty\RecurringPayments\Model\Subscription\Scheduler\DateTimeInterval;
use Magento\Quote\Api\Data\CartItemInterface;

class StartEndDateValidator
{
    const ERROR_START_DATE = 1;
    const ERROR_END_DATE = 2;
    const ERROR_COUNT_CYCLES = 3;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var StartEndDateMapper
     */
    private $startEndDateMapper;

    /**
     * @var DateTimeInterval
     */
    private $dateTimeInterval;

    /**
     * @var Product
     */
    private $recurringProduct;

    /**
     * @var ItemDataRetriever
     */
    private $itemDataRetriever;

    public function __construct(
        Config $configProvider,
        StartEndDateMapper $startEndDateMapper,
        DateTimeInterval $dateTimeInterval,
        Product $recurringProduct,
        ItemDataRetriever $itemDataRetriever
    ) {
        $this->configProvider = $configProvider;
        $this->startEndDateMapper = $startEndDateMapper;
        $this->dateTimeInterval = $dateTimeInterval;
        $this->recurringProduct = $recurringProduct;
        $this->itemDataRetriever = $itemDataRetriever;
    }

    /**
     * @param CartItemInterface $item
     */
    public function validate(CartItemInterface $item)
    {
        if (!$this->configProvider->isAllowSpecifyStartEndDate()) {
            return;
        }

        if (!$this->validateStartDate($item)) {
            $item->addErrorInfo(
                'amasty_recurring',
                self::ERROR_START_DATE,
                __('Subscription Start Date incorrect')
            );
            $item->getQuote()->addErrorInfo(
                'amasty_recurring',
                'amasty_recurring',
                self::ERROR_START_DATE,
                __('Some of the product options are invalid')
            );
        }

        if (!$this->validateEndDate($item)) {
            $item->addErrorInfo(
                'amasty_recurring',
                self::ERROR_END_DATE,
                __('Subscription End Date incorrect')
            );
            $item->getQuote()->addErrorInfo(
                'amasty_recurring',
                'amasty_recurring',
                self::ERROR_END_DATE,
                __('Some of the product options are invalid')
            );
        }

        if (!$this->validateCountCycles($item)) {
            $item->addErrorInfo(
                'amasty_recurring',
                self::ERROR_COUNT_CYCLES,
                __('End by a cycle should be greater than 1')
            );
            $item->getQuote()->addErrorInfo(
                'amasty_recurring',
                'amasty_recurring',
                self::ERROR_COUNT_CYCLES,
                __('Some of the product options are invalid')
            );
        }
    }

    /**
     * @param CartItemInterface $item
     * @return bool
     */
    private function validateStartDate(CartItemInterface $item): bool
    {
        $startDate = $this->startEndDateMapper->getSpecifiedStartDate($item);
        if (!$startDate) {
            return true;
        }

        $now = new \DateTime(date('Y-m-d'));

        if ($startDate < $now) {
            return false;
        }

        return true;
    }

    /**
     * @param CartItemInterface $item
     * @return bool
     */
    private function validateEndDate(CartItemInterface $item): bool
    {
        $endDate = $this->startEndDateMapper->getSpecifiedEndDate($item);
        if (!$endDate) {
            return true;
        }
        $timezone = $this->startEndDateMapper->getSpecifiedTimezoneOrUTC($item);

        $now = new \DateTime('now');

        if ($endDate < $now) {
            return false;
        }

        $startDate = $this->startEndDateMapper->getSpecifiedStartDate($item);
        !$startDate && $startDate = new \DateTime('now', $timezone);

        $plan = $this->itemDataRetriever->getPlan($item);
        if (!$plan) {
            return false;
        }
        $trialDays = $plan->getEnableTrial() && $plan->getTrialDays()
            ? $plan->getTrialDays()
            : 0;
        $startDateAfterTrial = $this->dateTimeInterval->getStartDateAfterTrial(
            $startDate->format('Y-m-d'),
            $trialDays
        );

        $dateAfterOnePeriod = $this->dateTimeInterval->getNextBillingDate(
            $startDateAfterTrial,
            $plan->getFrequency(),
            $plan->getFrequencyUnit()
        );

        $dateAfterOnePeriodObject = new \DateTime($dateAfterOnePeriod, $timezone);

        if ($dateAfterOnePeriodObject > $endDate) {
            return false;
        }

        return true;
    }

    /**
     * @param CartItemInterface $item
     * @return bool
     */
    private function validateCountCycles(CartItemInterface $item)
    {
        $requestOptions = $item->getBuyRequest()->getData();

        if (array_key_exists(Product::COUNT_CYCLES, $requestOptions)) {
            $value = $requestOptions[Product::COUNT_CYCLES];
            if ($value < 2) {
                return false;
            }
        }

        return true;
    }
}
