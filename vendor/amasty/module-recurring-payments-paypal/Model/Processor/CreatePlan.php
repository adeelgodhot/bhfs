<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\Processor;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\Subscription\Scheduler\DateTimeInterval;
use Amasty\RecurringPaypal\Model\Api\Adapter;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Api\Data\OrderInterface;

class CreatePlan extends AbstractProcessor
{
    const CYCLE_TYPE_TRIAL = 'TRIAL';
    const CYCLE_TYPE_REGULAR = 'REGULAR';
    const TOTAL_CYCLES_INFINITE = 0;

    /**
     * @var DateTimeInterval
     */
    private $dateTimeInterval;

    public function __construct(
        Adapter $adapter,
        DateTimeInterval $dateTimeInterval
    ) {
        parent::__construct($adapter);
        $this->dateTimeInterval = $dateTimeInterval;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param QuoteItem $item
     * @param string $productId
     * @param OrderInterface $order
     * @return array
     */
    public function execute(
        SubscriptionInterface $subscription,
        QuoteItem $item,
        string $productId,
        OrderInterface $order
    ): array {
        $cycles = [];
        $trialDays = $subscription->getTrialDays();
        $frequency = $subscription->getFrequency();
        $frequencyUnit = $subscription->getFrequencyUnit();
        if ($trialDays) {
            $cycles[] = [
                'frequency' => [
                    'interval_unit' => 'DAY',
                    'interval_count' => $trialDays,
                ],
                'tenure_type' => self::CYCLE_TYPE_TRIAL,
                'total_cycles' => 1,
            ];
        }

        $cycle = [
            'frequency' => [
                'interval_unit' => strtoupper($frequencyUnit),
                'interval_count' => $frequency,
            ],
            'tenure_type' => self::CYCLE_TYPE_REGULAR,
            'total_cycles' => self::TOTAL_CYCLES_INFINITE,
            'pricing_scheme' => [
                'fixed_price' => [
                    'value' => $subscription->getBaseGrandTotalWithDiscount(),
                    'currency_code' => $order->getBaseCurrencyCode(),
                ],
            ],
        ];

        $startDate = $subscription->getStartDate();
        $endDate = $subscription->getEndDate();

        if ($endDate) {
            if ($trialDays) {
                $startDate = $this->dateTimeInterval->getStartDateAfterTrial(
                    $startDate,
                    $trialDays
                );
            }

            $countIntervalsBeforeCancel = $this->dateTimeInterval->getCountIntervalsBetweenDates(
                $startDate,
                $endDate,
                $frequency,
                $frequencyUnit
            );
            $cycle['total_cycles'] = $countIntervalsBeforeCancel;
        }

        $cycles[] = $cycle;

        $sequence = 1;
        foreach ($cycles as &$cycle) {
            $cycle['sequence'] = $sequence++;
        }

        return $this->adapter->createPlan([
            'product_id' => $productId,
            'name' => $item->getName(),
            'billing_cycles' => $cycles,
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
            ],
        ]);
    }
}
