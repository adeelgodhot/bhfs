<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\Scheduler;

use Amasty\RecurringPayments\Model\Config\Source\BillingFrequencyUnit;

class DateTimeInterval
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param string $startDate
     * @param int $trialDays
     *
     * @return bool
     */
    public function isTrialPeriodActive(string $startDate, int $trialDays): bool
    {
        if (!$trialDays) {
            return false;
        }

        $date = new \DateTime($startDate);
        $date->modify('+' . $trialDays . ' days');

        return $date->getTimestamp() > time();
    }

    /**
     * @param string $startDate
     * @param int $trialDays
     *
     * @return string
     */
    public function getStartDateAfterTrial(string $startDate, int $trialDays): string
    {
        return $this->getNextBillingDate(
            $startDate,
            $trialDays,
            BillingFrequencyUnit::DAY
        );
    }

    /**
     * @param string $startDate
     * @param int $interval
     * @param string $intervalUnits
     * @param int $countIntervals
     *
     * @param bool $excludeStartDate
     * @return array
     * @throws \Exception
     */
    public function getDateRangeForSubscription(
        string $startDate,
        int $interval,
        string $intervalUnits,
        int $countIntervals,
        bool $excludeStartDate = true
    ): array {
        $generator = $this->getDatesGenerator(
            $startDate,
            $interval,
            $intervalUnits,
            $countIntervals,
            $excludeStartDate
        );
        $listDates = [];

        foreach ($generator as $date) {
            $listDates[] = $date->format(self::DATE_FORMAT);
        }

        if (!$excludeStartDate) {
            array_pop($listDates);
        }

        return $listDates;
    }

    /**
     * @param string $lastPaymentDate
     * @param int $interval
     * @param string $intervalUnit
     *
     * @return string
     */
    public function getNextBillingDate(string $lastPaymentDate, int $interval, string $intervalUnit): string
    {
        $generator = $this->getDatesGenerator(
            $lastPaymentDate,
            $interval,
            $intervalUnit,
            1,
            true
        );
        $date = $generator->current();

        return $date->format(self::DATE_FORMAT);
    }

    /**
     * @param string $startDate
     * @param int $interval
     * @param string $intervalUnits
     * @param int $countIntervals
     * @return string
     */
    public function getLastDateOfInterval(
        string $startDate,
        int $interval,
        string $intervalUnits,
        int $countIntervals
    ): string {
        $generator = $this->getDatesGenerator(
            $startDate,
            $interval,
            $intervalUnits,
            $countIntervals,
            true
        );
        $endDate = "";

        foreach ($generator as $date) {
            $endDate = $date->format(self::DATE_FORMAT);
        }

        return $endDate;
    }

    /**
     * @param string $startDate
     * @param string $endDate
     * @param int $interval
     * @param string $intervalUnit
     * @return int
     */
    public function getCountIntervalsBetweenDates(
        string $startDate,
        string $endDate,
        int $interval,
        string $intervalUnit
    ) {
        $endDateObject = new \DateTime($endDate);
        $counter = 0;
        while (true) {
            $generator = $this->getDatesGenerator(
                $startDate,
                $interval,
                $intervalUnit,
                1000,
                true
            );

            foreach ($generator as $item) {
                /** @var \DateTime $item */
                if ($item > $endDateObject) {
                    break(2);
                }
                $counter++;
                $startDate = $item->format(self::DATE_FORMAT);
            }

            if ($counter >= PHP_INT_MAX) {
                break;
            }
        }

        return $counter;
    }

    /**
     * @param \DateTime $from
     * @param \DateInterval $interval
     * @param int $count
     * @param bool $excludeStartDate
     * @return \Generator
     */
    private function getDateRange(\DateTime $from, \DateInterval $interval, int $count, bool $excludeStartDate)
    {
        $datePeriod = new \DatePeriod(
            $from,
            $interval,
            $count,
            $excludeStartDate ? \DatePeriod::EXCLUDE_START_DATE : 0
        );
        foreach ($datePeriod as $date) {
            yield $date;
        }
    }

    /**
     * @param \DateTime $from
     * @param \DateInterval $interval
     * @param int $count
     * @param bool $excludeStartDate
     * @return \Generator
     */
    private function getDateRangeForMonths(
        \DateTime $from,
        \DateInterval $interval,
        int $count,
        bool $excludeStartDate
    ) {
        $day = $from->format('j');
        $from->modify('first day of this month');

        $period = new \DatePeriod(
            $from,
            $interval,
            $count,
            $excludeStartDate ? \DatePeriod::EXCLUDE_START_DATE : 0
        );

        foreach ($period as $date) {
            $lastDay = clone $date;
            $lastDay->modify('last day of this month');
            $date->setDate((int)$date->format('Y'), (int)$date->format('n'), (int)$day);
            if ($date > $lastDay) {
                $date = $lastDay;
            }
            yield $date;
        }
    }

    /**
     * @param int $interval
     * @param string $intervalUnits
     * @return \DateInterval
     */
    private function getDateInterval(int $interval, string $intervalUnits): \DateInterval
    {
        $modifyInterval = 'P' . $interval;
        switch ($intervalUnits) {
            case BillingFrequencyUnit::DAY:
                $modifyInterval .= 'D';
                break;
            case BillingFrequencyUnit::WEEK:
                $modifyInterval .= 'W';
                break;
            case BillingFrequencyUnit::MONTH:
                $modifyInterval .= 'M';
                break;
            case BillingFrequencyUnit::YEAR:
                $modifyInterval .= 'Y';
                break;
        }

        return new \DateInterval($modifyInterval);
    }

    /**
     * @param string $startDate
     * @param int $interval
     * @param string $intervalUnits
     * @param int $countIntervals
     * @param bool $excludeStartDate
     * @return \Generator
     */
    private function getDatesGenerator(
        string $startDate,
        int $interval,
        string $intervalUnits,
        int $countIntervals,
        bool $excludeStartDate = true
    ) {
        $dateInterval = $this->getDateInterval($interval, $intervalUnits);
        $startDate = $this->prepareStartDate($startDate, $intervalUnits == BillingFrequencyUnit::YEAR);

        if ($intervalUnits == BillingFrequencyUnit::MONTH) {
            $generator = $this->getDateRangeForMonths(
                $startDate,
                $dateInterval,
                $countIntervals,
                $excludeStartDate
            );
        } else {
            $generator = $this->getDateRange(
                $startDate,
                $dateInterval,
                $countIntervals,
                $excludeStartDate
            );
        }

        return $generator;
    }

    /**
     * @param string $startDate
     * @param bool $fix29Feb
     * @return \DateTime
     */
    private function prepareStartDate(string $startDate, bool $fix29Feb): \DateTime
    {
        $startDate = new \DateTime($startDate);
        // fix for 29 Feb
        if ($fix29Feb
            && $startDate->format('d') == '29'
            && $startDate->format('m') == '02'
        ) {
            $startDate->modify('-1 day');
        }

        return $startDate;
    }
}
