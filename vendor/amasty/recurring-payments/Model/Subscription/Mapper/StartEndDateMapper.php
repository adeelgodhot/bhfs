<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Subscription\Mapper;

use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Date;
use Amasty\RecurringPayments\Model\Product;
use Amasty\RecurringPayments\Model\Quote\ItemDataRetriever;
use Amasty\RecurringPayments\Model\Subscription\Scheduler\DateTimeInterval;
use Magento\Framework\Stdlib\DateTime;
use Magento\Quote\Api\Data\CartItemInterface;

class StartEndDateMapper
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var DateTimeInterval
     */
    private $dateTimeInterval;

    /**
     * @var Product
     */
    private $recurringProduct;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ItemDataRetriever
     */
    private $itemDataRetriever;

    public function __construct(
        Config $configProvider,
        Date $date,
        DateTimeInterval $dateTimeInterval,
        DateTime $dateTime,
        Product $recurringProduct,
        ItemDataRetriever $itemDataRetriever
    ) {
        $this->configProvider = $configProvider;
        $this->date = $date;
        $this->dateTimeInterval = $dateTimeInterval;
        $this->recurringProduct = $recurringProduct;
        $this->dateTime = $dateTime;
        $this->itemDataRetriever = $itemDataRetriever;
    }

    /**
     * @param CartItemInterface $item
     * @return array
     */
    public function getStartEndDate(CartItemInterface $item)
    {
        list($startDate, $endDate) = $this->getSpecifiedStartEndDates($item);

        $startDate = $this->date->date(null, $startDate ? $startDate->getTimestamp() : null);

        if ($endDate) {
            $endDate = $this->date->date(null, $endDate->getTimestamp());
        }

        return [$startDate, $endDate];
    }

    /**
     * @param CartItemInterface $item
     * @return \DateTime[]|array
     */
    public function getSpecifiedStartEndDates(CartItemInterface $item)
    {
        if (!$this->configProvider->isAllowSpecifyStartEndDate()) {
            return [null, null];
        }
        $startDate = $this->getSpecifiedStartDate($item);
        $requestOptions = $item->getBuyRequest()->getData();
        if (!empty($requestOptions[Product::COUNT_CYCLES]) && $requestOptions[Product::COUNT_CYCLES] > 1) {
            $plan = $this->itemDataRetriever->getPlan($item);
            if (!$plan) {
                return [null, null];
            }
            $trialDays = $plan->getEnableTrial() && $plan->getTrialDays()
                ? $plan->getTrialDays()
                : 0;
            $startDateForCount = $startDate ? $startDate->format('Y-m-d H:i:s') : $this->date->date();
            $startDateForCount = $this->dateTimeInterval->getStartDateAfterTrial(
                $startDateForCount,
                $trialDays
            );
            $endDate = $this->dateTimeInterval->getLastDateOfInterval(
                $startDateForCount,
                $plan->getFrequency(),
                $plan->getFrequencyUnit(),
                (int)($requestOptions[Product::COUNT_CYCLES] -1)
            );
            $endDate = $this->validateAndFormatDate(
                $endDate,
                $this->getSpecifiedTimezoneOrUTC($item)
            );
        } else {
            $endDate = $this->getSpecifiedEndDate($item);
        }

        return [$startDate, $endDate];
    }

    /**
     * @param CartItemInterface $item
     * @return \DateTime|null
     */
    public function getSpecifiedStartDate(CartItemInterface $item)
    {
        $requestOptions = $item->getBuyRequest()->getData();

        return $this->validateAndFormatDate(
            $requestOptions[Product::START_DATE] ?? null,
            $this->getSpecifiedTimezoneOrUTC($item)
        );
    }

    /**
     * @param CartItemInterface $item
     * @return \DateTimeZone
     */
    public function getSpecifiedTimezoneOrUTC(CartItemInterface $item): \DateTimeZone
    {
        $timezone = $this->getSpecifiedTimezone($item);
        if ($timezone === null) {
            $timezone = 'UTC';
        }

        return $this->getTimezoneObject($timezone);
    }

    /**
     * @param CartItemInterface $item
     * @return string|null
     */
    public function getSpecifiedTimezone(CartItemInterface $item)
    {
        $requestOptions = $item->getBuyRequest()->getData();

        if (!isset($requestOptions[Product::TIMEZONE])) {
            return null;
        }

        $isNegative = false;
        $minutesOffsetFromUtc = $requestOptions[Product::TIMEZONE];
        if ($minutesOffsetFromUtc < 0) {
            $isNegative = true;
            $minutesOffsetFromUtc *= -1;
        }
        $minutes = $minutesOffsetFromUtc % 60;
        $hours = ($minutesOffsetFromUtc - $minutes) / 60;

        $hours = str_pad($hours, 2, "0", STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, "0", STR_PAD_LEFT);

        $timezoneOffset = ($isNegative ? '-' : '+') . $hours . ':' . $minutes;

        $timezone = $this->getTimezoneObject(
            $timezoneOffset
        );

        if ($timezone !== null) {
            $timezone = $timezone->getName();
        }

        return $timezone;
    }

    /**
     * @param CartItemInterface $item
     * @return \DateTime|null
     */
    public function getSpecifiedEndDate(CartItemInterface $item)
    {
        $requestOptions = $item->getBuyRequest()->getData();

        return $this->validateAndFormatDate(
            $requestOptions[Product::END_DATE] ?? null,
            $this->getSpecifiedTimezoneOrUTC($item)
        );
    }

    /**
     * @param string|null $value
     * @param \DateTimeZone $timezone
     * @return \DateTime|null
     */
    private function validateAndFormatDate($value, $timezone)
    {
        if (empty($value)) {
            return null;
        }

        $date = $this->getDateTimeObject($value, $timezone);

        if (!$date) {
            return null;
        }

        if ($date->format('H:i:s') == '00:00:00') {
            $now = $this->getDateTimeObject('now', $timezone);
            $value .= $now->format(' H:i:s');
            $date = $this->getDateTimeObject($value, $timezone);
        }

        return $date;
    }

    /**
     * @param string|null $timezone
     * @return \DateTimeZone
     */
    private function getTimezoneObject($timezone)
    {
        try {
            $timezoneObject = new \DateTimeZone($timezone);
        } catch (\Throwable $e) {
            return null;
        }

        return $timezoneObject;
    }

    /**
     * @param string $date
     * @param null $timezone
     * @return \DateTime|null
     */
    private function getDateTimeObject($date, $timezone = null)
    {
        try {
            $date = new \DateTime($date, $timezone);
        } catch (\Throwable $e) {
            return null;
        }

        return $date;
    }
}
