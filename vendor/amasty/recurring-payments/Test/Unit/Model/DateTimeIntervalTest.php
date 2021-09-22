<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Test\Unit\Model;

use Amasty\RecurringPayments\Model\Config\Source\BillingFrequencyUnit;
use Amasty\RecurringPayments\Model\Subscription\Scheduler\DateTimeInterval;

/**
 * Class DateTimeIntervalTest
 *
 * @see DateTimeInterval
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DateTimeIntervalTest extends \PHPUnit\Framework\TestCase
{
    /** @var DateTimeInterval */
    protected $dateTimeInterval;

    public function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dateTimeInterval = $objectManager->getObject(DateTimeInterval::class);
    }

    /**
     * @covers DateTimeInterval::isTrialPeriodActive
     */
    public function testIsTrialPeriodActive()
    {
        $this->assertFalse($this->dateTimeInterval->isTrialPeriodActive('', 0));

        $dateTimeNow = new \DateTime();
        $this->assertTrue(
            $this->dateTimeInterval->isTrialPeriodActive(
                $dateTimeNow->format('Y-m-d H:i:s'),
                3
            )
        );

        $dateTimeThreeDaysBeforeNow = new \DateTime();
        $dateTimeThreeDaysBeforeNow->modify('-3 days');

        $this->assertFalse(
            $this->dateTimeInterval->isTrialPeriodActive(
                $dateTimeThreeDaysBeforeNow->format('Y-m-d H:i:s'),
                3
            )
        );
    }

    /**
     * @covers DateTimeInterval::getStartDateAfterTrial
     * @dataProvider dataProviderForGetStartDateAfterTrial
     */
    public function testGetStartDateAfterTrial($startDate, $trialDays, $result)
    {
        $this->assertEquals(
            $result,
            $this->dateTimeInterval->getStartDateAfterTrial($startDate, $trialDays)
        );

    }

    /**
     * @covers DateTimeInterval::getDateRangeForSubscription
     * @dataProvider dataProviderForGetDateRangeForSubscription
     */
    public function testGetDateRangeForSubscription(
        $startDate,
        $interval,
        $intervalUnits,
        $countIntervals,
        $excludeStartDate,
        $result
    ) {
        $this->assertEquals(
            $result,
            $this->dateTimeInterval->getDateRangeForSubscription(
                $startDate,
                $interval,
                $intervalUnits,
                $countIntervals,
                $excludeStartDate
            )
        );
    }

    /**
     * @covers DateTimeInterval::getNextBillingDate
     * @dataProvider dataProviderForGetNextBillingDate
     */
    public function testGetNextBillingDate($lastPaymentDate, $interval, $intervalUnit, $result)
    {
        $this->assertEquals(
            $result,
            $this->dateTimeInterval->getNextBillingDate(
                $lastPaymentDate,
                $interval,
                $intervalUnit
            )
        );
    }

    /**
     * @covers DateTimeInterval::getLastDateOfInterval
     * @dataProvider dataProviderForGetLastDateOfInterval
     */
    public function testGetLastDateOfInterval(
        $startDate,
        $interval,
        $intervalUnits,
        $countIntervals,
        $result
    ) {
        $this->assertEquals(
            $result,
            $this->dateTimeInterval->getLastDateOfInterval(
                $startDate,
                $interval,
                $intervalUnits,
                $countIntervals
            )
        );
    }

    /**
     * @covers DateTimeInterval::getCountIntervalsBetweenDates
     * @dataProvider dataProviderForGetCountIntervalsBetweenDates
     */
    public function testGetCountIntervalsBetweenDates($startDate, $endDate, $interval, $intervalUnit, $result)
    {
        $this->assertEquals(
            $result,
            $this->dateTimeInterval->getCountIntervalsBetweenDates(
                $startDate,
                $endDate,
                $interval,
                $intervalUnit
            )
        );
    }

    /**
     * @return array
     */
    public function dataProviderForGetStartDateAfterTrial()
    {
        return [
            ['2020-01-31 00:00:00', 1, '2020-02-01 00:00:00'],
            ['2020-02-27 00:00:00', 2, '2020-02-29 00:00:00'],
            ['2019-02-28 00:00:00', 1, '2019-03-01 00:00:00'],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderForGetDateRangeForSubscription()
    {
        return [
            [
                '2020-01-31 00:00:00',
                1,
                BillingFrequencyUnit::DAY,
                3,
                true,
                [
                    '2020-02-01 00:00:00',
                    '2020-02-02 00:00:00',
                    '2020-02-03 00:00:00',
                ]
            ],

            [
                '2020-01-31 00:00:00',
                1,
                BillingFrequencyUnit::DAY,
                3,
                false,
                [
                    '2020-01-31 00:00:00',
                    '2020-02-01 00:00:00',
                    '2020-02-02 00:00:00',
                ]
            ],

            [
                '2020-01-31 00:00:00',
                1,
                BillingFrequencyUnit::WEEK,
                3,
                true,
                [
                    '2020-02-07 00:00:00',
                    '2020-02-14 00:00:00',
                    '2020-02-21 00:00:00',
                ]
            ],

            [
                '2020-01-31 00:00:00',
                1,
                BillingFrequencyUnit::MONTH,
                3,
                true,
                [
                    '2020-02-29 00:00:00',
                    '2020-03-31 00:00:00',
                    '2020-04-30 00:00:00',
                ]
            ],

            [
                '2020-02-29 00:00:00',
                1,
                BillingFrequencyUnit::YEAR,
                3,
                true,
                [
                    '2021-02-28 00:00:00',
                    '2022-02-28 00:00:00',
                    '2023-02-28 00:00:00',
                ]
            ],

            [
                '2020-02-28 00:00:00',
                1,
                BillingFrequencyUnit::YEAR,
                3,
                false,
                [
                    '2020-02-28 00:00:00',
                    '2021-02-28 00:00:00',
                    '2022-02-28 00:00:00',
                ]
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderForGetNextBillingDate()
    {
        return [
            [
                '2020-01-31 00:00:00',
                1,
                BillingFrequencyUnit::DAY,
                '2020-02-01 00:00:00',
            ],

            [
                '2020-01-31 00:00:00',
                1,
                BillingFrequencyUnit::MONTH,
                '2020-02-29 00:00:00',
            ],

            [
                '2019-01-31 00:00:00',
                1,
                BillingFrequencyUnit::MONTH,
                '2019-02-28 00:00:00',
            ],

            [
                '2019-01-31 23:00:00',
                2,
                BillingFrequencyUnit::DAY,
                '2019-02-02 23:00:00',
            ],

            [
                '2019-01-31 23:00:00',
                3,
                BillingFrequencyUnit::DAY,
                '2019-02-03 23:00:00',
            ],
        ];
    }

    /**
     * @return array
     */
    public function dataProviderForGetLastDateOfInterval()
    {
        return [
            [
                '2020-01-31 00:00:00',
                1,
                BillingFrequencyUnit::DAY,
                3,
                '2020-02-03 00:00:00',
            ],

            [
                '2020-01-31 00:00:00',
                1,
                BillingFrequencyUnit::WEEK,
                3,
                '2020-02-21 00:00:00',
            ],

            [
                '2020-01-31 00:00:00',
                1,
                BillingFrequencyUnit::MONTH,
                3,
                '2020-04-30 00:00:00',
            ],

            [
                '2020-02-29 00:00:00',
                1,
                BillingFrequencyUnit::YEAR,
                3,
                '2023-02-28 00:00:00',
            ],
        ];
    }

    public function dataProviderForGetCountIntervalsBetweenDates()
    {
        return [
            'oneDayOneInterval' => [
                '2020-01-01 00:00:00',
                '2020-01-02 00:00:00',
                1,
                BillingFrequencyUnit::DAY,
                1
            ],
            'oneDay3Intervals' => [
                '2020-01-31 00:00:00',
                '2020-02-03 00:00:00',
                1,
                BillingFrequencyUnit::DAY,
                3
            ],
            'oneDay30Intervals' => [
                '2020-01-01 00:00:00',
                '2020-01-31 00:00:00',
                1,
                BillingFrequencyUnit::DAY,
                30
            ],
            [
                '2020-02-01 00:00:00',
                '2020-03-01 00:00:00',
                1,
                BillingFrequencyUnit::MONTH,
                1
            ],

            [
                '2020-02-01 00:00:00',
                '2020-03-15 00:00:00',
                1,
                BillingFrequencyUnit::MONTH,
                1
            ],
            [
                '2020-02-01 00:00:00',
                '2020-02-15 00:00:00',
                1,
                BillingFrequencyUnit::WEEK,
                2
            ],
        ];
    }

}
