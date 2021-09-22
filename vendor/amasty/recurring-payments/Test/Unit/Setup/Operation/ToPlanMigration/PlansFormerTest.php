<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Test\Unit\Setup\Operation\ToPlanMigration;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Model\Config\Source\AmountType;
use Amasty\RecurringPayments\Model\Config\Source\BillingCycle;
use Amasty\RecurringPayments\Model\Config\Source\BillingFrequencyUnit;
use Amasty\RecurringPayments\Model\Config\Source\EnableDisable;
use Amasty\RecurringPayments\Model\Config\Source\PlanStatus;
use Amasty\RecurringPayments\Model\Subscription\Mapper\BillingFrequencyLabelMapper;
use Amasty\RecurringPayments\Setup\Operation\ToPlanMigration\PlansFormer;

/**
 * Class PlansFormerTest
 *
 * @see PlansFormer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class PlansFormerTest extends \PHPUnit\Framework\TestCase
{

    /** @var PlansFormer */
    protected $plansFormer;

    public function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $billingFrequencyLabelMapper = $objectManager->getObject(BillingFrequencyLabelMapper::class);
        $this->plansFormer = $objectManager->getObject(PlansFormer::class, [
            'billingFrequencyLabelMapper' => $billingFrequencyLabelMapper
        ]);
    }

    /**
     * @covers PlansFormer::convertToPlanData
     * @dataProvider dataProvider
     */
    public function testConvertToPlanData(array $input, string $entityType, array $expectedResult)
    {
        $result = $this->plansFormer->convertToPlanData($input, $entityType);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return [
            'justBillingCycle' => [
                [
                    'default_0' => [
                        'billing_cycle' => BillingCycle::ONCE_DAY
                    ]
                ],
                PlansFormer::TYPE_CONFIG,
                [
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Daily',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                        ],
                        'type' => PlansFormer::TYPE_CONFIG,
                        'entity' => 'default',
                        'store_id' => '0',
                    ]
                ]
            ],
            'customBillingCycle' => [
                [
                    'default_0' => [
                        'billing_cycle' => BillingCycle::CUSTOM,
                        'billing_frequency' => 3,
                        'billing_frequency_unit' => BillingFrequencyUnit::DAY
                    ]
                ],
                PlansFormer::TYPE_CONFIG,
                [
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Every 3 days',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 3,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                        ],
                        'type' => PlansFormer::TYPE_CONFIG,
                        'entity' => 'default',
                        'store_id' => '0',
                    ]
                ]
            ],
            'customerDecide' => [
                [
                    'default_0' => [
                        'billing_cycle' => BillingCycle::CUSTOMER_DECIDE
                    ]
                ],
                PlansFormer::TYPE_CONFIG,
                [
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Daily',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                        ],
                        'type' => PlansFormer::TYPE_CONFIG,
                        'entity' => 'default',
                        'store_id' => '0',
                    ],
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Weekly',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::WEEK,
                        ],
                        'type' => PlansFormer::TYPE_CONFIG,
                        'entity' => 'default',
                        'store_id' => '0',
                    ],
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Monthly',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::MONTH,
                        ],
                        'type' => PlansFormer::TYPE_CONFIG,
                        'entity' => 'default',
                        'store_id' => '0',
                    ],
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Annual',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::YEAR,
                        ],
                        'type' => PlansFormer::TYPE_CONFIG,
                        'entity' => 'default',
                        'store_id' => '0',
                    ]
                ]
            ],

            'productWithTrial' => [
                [
                    '3_0' => [
                        'billing_cycle' => BillingCycle::ONCE_DAY,
                        'enable_free_trials' => EnableDisable::ENABLE,
                        'number_of_trial_days' => 2,
                    ],
                    '2_1' => [
                        'billing_cycle' => BillingCycle::ONCE_DAY,
                        'enable_free_trials' => EnableDisable::ENABLE,
                        'number_of_trial_days' => 0,
                    ]
                ],
                PlansFormer::TYPE_PRODUCT,
                [
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Daily with trial',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 1,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                            SubscriptionPlanInterface::TRIAL_DAYS => 2,
                        ],
                        'type' => PlansFormer::TYPE_PRODUCT,
                        'entity' => '3',
                        'store_id' => '0',
                    ],
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Daily',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                        ],
                        'type' => PlansFormer::TYPE_PRODUCT,
                        'entity' => '2',
                        'store_id' => '1',
                    ]
                ]
            ],

            'productWithFee' => [
                [
                    '3_0' => [
                        'billing_cycle' => BillingCycle::ONCE_DAY,
                        'charge_initial_fee' => 1,
                        'initial_fee_type' => AmountType::FIXED_AMOUNT,
                        'initial_fee_amount' => 2.0,
                    ],
                    '2_1' => [
                        'billing_cycle' => BillingCycle::ONCE_DAY,
                        'charge_initial_fee' => 1,
                        'initial_fee_type' => AmountType::PERCENT_AMOUNT,
                        'initial_fee_amount' => 25.0,
                    ],
                    '1_2' => [
                        'billing_cycle' => BillingCycle::ONCE_DAY,
                        'charge_initial_fee' => 1,
                        'initial_fee_type' => AmountType::PERCENT_AMOUNT,
                        'initial_fee_amount' => 0,
                    ]
                ],
                PlansFormer::TYPE_PRODUCT,
                [
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Daily with fixed initial fee',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 1,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                            SubscriptionPlanInterface::INITIAL_FEE_TYPE => AmountType::FIXED_AMOUNT,
                            SubscriptionPlanInterface::INITIAL_FEE_AMOUNT => 2.0,
                        ],
                        'type' => PlansFormer::TYPE_PRODUCT,
                        'entity' => '3',
                        'store_id' => '0',
                    ],
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Daily with percentage initial fee',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 1,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                            SubscriptionPlanInterface::INITIAL_FEE_TYPE => AmountType::PERCENT_AMOUNT,
                            SubscriptionPlanInterface::INITIAL_FEE_AMOUNT => 25.0,
                        ],
                        'type' => PlansFormer::TYPE_PRODUCT,
                        'entity' => '2',
                        'store_id' => '1',
                    ],
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Daily',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                        ],
                        'type' => PlansFormer::TYPE_PRODUCT,
                        'entity' => '1',
                        'store_id' => '2',
                    ]
                ]
            ],

            'productWithDiscount' => [
                [
                    '3_0' => [
                        'billing_cycle' => BillingCycle::ONCE_DAY,
                        'discounted_prices' => 1,
                        'discount_type' => AmountType::FIXED_AMOUNT,
                        'discount_amount' => 2.0,
                    ],
                    '2_1' => [
                        'billing_cycle' => BillingCycle::ONCE_DAY,
                        'discounted_prices' => 1,
                        'discount_type' => AmountType::PERCENT_AMOUNT,
                        'discount_amount_percent' => 25.0,
                    ],
                    '1_2' => [
                        'billing_cycle' => BillingCycle::ONCE_DAY,
                        'discounted_prices' => 1,
                        'discount_type' => AmountType::PERCENT_AMOUNT,
                        'discount_amount' => 0,
                    ],
                    '0_3' => [
                        'billing_cycle' => BillingCycle::ONCE_DAY,
                        'discounted_prices' => 1,
                        'discount_type' => AmountType::FIXED_AMOUNT,
                        'discount_amount' => 2.0,
                        'enable_limit_discounted_cycles' => 1,
                        'number_discounted_cycles' => 1,
                    ],
                ],
                PlansFormer::TYPE_PRODUCT,
                [
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Daily with fixed discount',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 1,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                            SubscriptionPlanInterface::DISCOUNT_TYPE => AmountType::FIXED_AMOUNT,
                            SubscriptionPlanInterface::DISCOUNT_AMOUNT => 2.0,
                        ],
                        'type' => PlansFormer::TYPE_PRODUCT,
                        'entity' => '3',
                        'store_id' => '0',
                    ],
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Daily with percentage discount',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 1,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                            SubscriptionPlanInterface::DISCOUNT_TYPE => AmountType::PERCENT_AMOUNT,
                            SubscriptionPlanInterface::DISCOUNT_AMOUNT => 25.0,
                        ],
                        'type' => PlansFormer::TYPE_PRODUCT,
                        'entity' => '2',
                        'store_id' => '1',
                    ],
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Daily',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 0,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                        ],
                        'type' => PlansFormer::TYPE_PRODUCT,
                        'entity' => '1',
                        'store_id' => '2',
                    ],
                    [
                        'plan' => [
                            SubscriptionPlanInterface::NAME => 'Daily with limited fixed discount',
                            SubscriptionPlanInterface::ENABLE_TRIAL => 0,
                            SubscriptionPlanInterface::ENABLE_INITIAL_FEE => 0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT => 1,
                            SubscriptionPlanInterface::STATUS => PlanStatus::ACTIVE,
                            SubscriptionPlanInterface::FREQUENCY => 1,
                            SubscriptionPlanInterface::FREQUENCY_UNIT => BillingFrequencyUnit::DAY,
                            SubscriptionPlanInterface::DISCOUNT_TYPE => AmountType::FIXED_AMOUNT,
                            SubscriptionPlanInterface::DISCOUNT_AMOUNT => 2.0,
                            SubscriptionPlanInterface::ENABLE_DISCOUNT_LIMIT => 1,
                            SubscriptionPlanInterface::NUMBER_DISCOUNT_CYCLES => 1,
                        ],
                        'type' => PlansFormer::TYPE_PRODUCT,
                        'entity' => '0',
                        'store_id' => '3',
                    ],
                ]
            ],

        ];
    }
}
