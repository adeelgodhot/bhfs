<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Test\Unit\Model\DateTime;

use Amasty\RecurringPayments\Model\DateTime\DateTimeComparer;

/**
 * Class DateTimeIntervalTest
 *
 * @see DateTimeComparer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class DateTimeComparerTest
{
    /** @var DateTimeComparer */
    protected $dateTimeComparer;

    public function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->dateTimeComparer = $objectManager->getObject(DateTimeComparer::class);
    }

    /**
     * @covers ItemComparer::compareWithProduct
     * @dataProvider dataProvider
     */
    public function testCompareDates($dateA, $dateB, $result)
    {
        $comparisonResult = $this->dateTimeComparer->compareDates($dateA, $dateB);
        $this->assertEquals($result, $comparisonResult);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'datesWithTimesTrue' => [
                '2012-02-20 23:00:00',
                '2012-02-20 12:00:00',
                true
            ],

            'datesWithTimesFalse' => [
                '2012-02-20 23:00:00',
                '2012-02-21 23:00:00',
                false
            ],

            'datesTrue' => [
                '2012-02-20',
                '2012-02-20',
                true
            ],

            'datesFalse' => [
                '2012-02-20',
                '2012-02-21',
                false
            ],

            'datesYearFalse' => [
                '2012-02-20',
                '2013-02-20',
                false
            ],

            'datesMonthFalse' => [
                '2012-02-20',
                '2012-03-20',
                false
            ],
        ];
    }
}
