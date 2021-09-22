<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Test\Unit\Model\Quote;

use Amasty\RecurringPayments\Model\Quote\ItemComparer;

/**
 * Class ItemComparerTest
 *
 * @see ItemComparer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class ItemComparerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ItemComparer */
    protected $itemComparer;

    public function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->itemComparer = $objectManager->getObject(ItemComparer::class);
    }

    /**
     * @covers ItemComparer::compareWithProduct
     * @dataProvider dataProvider
     */
    public function testCompareWithProduct($itemBuyRequestData, $productBuyRequestData, $result)
    {
        $comparisonResult = $this->itemComparer->compareWithProduct($itemBuyRequestData, $productBuyRequestData);
        $this->assertEquals($result, $comparisonResult);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'empty' => [
                [
                ],
                [
                ],
                true
            ],
            'notSubscribedProducts' => [
                [
                    'subscribe' => ''
                ],
                [
                    'subscribe' => ''
                ],
                true
            ],
            'onlysubscription1' => [
                [
                    'subscribe' => 'subscribe'
                ],
                [
                    'subscribe' => 'subscribe'
                ],
                true
            ],
            'onlysubscription2' => [
                [
                    'subscribe' => 'subscribe'
                ],
                [
                    'subscription_product' => '1'
                ],
                true
            ],
            'onlysubscription3' => [
                [
                    'subscription_product' => '1'
                ],
                [
                    'subscribe' => 'subscribe'
                ],
                true
            ],
            'onlysubscription4' => [
                [
                    'subscription_product' => '1'
                ],
                [
                    'subscription_product' => '1'
                ],
                true
            ],
            'diffBySubscription' => [
                [
                    'subscribe' => 'subscribe'
                ],
                [
                    'subscribe' => ''
                ],
                false
            ],

            'equalData' => [
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '1',
                ],
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '1',
                ],
                true
            ],

            'equalDataWithDifferentTrash' => [
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '1',
                    'form_key' => 'formKey1',
                ],
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '1',
                    'form_key' => 'formKey2',
                ],
                true
            ],

            'differentDataStartDate' => [
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '1',
                ],
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-21',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '1',
                ],
                false
            ],
            'differentDataEndDate' => [
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '1',
                ],
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-21',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '1',
                ],
                false
            ],
            'differentDataTimezone' => [
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '1',
                ],
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Moscow',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '1',
                ],
                false
            ],
            'differentDataCountCycles' => [
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '1',
                ],
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '4',
                    'am_rec_subscription_plan_id' => '1',
                ],
                false
            ],
            'differentDataPlan' => [
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '1',
                ],
                [
                    'subscribe' => 'subscribe',
                    'am_rec_start_date' => '2020-02-20',
                    'am_rec_end_date' => '2020-03-20',
                    'am_rec_timezone' => 'Europe/Minsk',
                    'am_rec_count_cycles' => '3',
                    'am_rec_subscription_plan_id' => '2',
                ],
                false
            ],
        ];
    }
}
