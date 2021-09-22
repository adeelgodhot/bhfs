<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Subscription\Mapper;

use Amasty\RecurringPayments\Model\Config\Source\BillingFrequencyUnit;

class BillingFrequencyLabelMapper
{
    /**
     * @param int $frequency
     * @param string $frequencyUnit
     * @return \Magento\Framework\Phrase
     */
    public function getLabel(int $frequency, string $frequencyUnit)
    {
        if ($frequency == 1) {
            $mapLabels = [
                BillingFrequencyUnit::DAY => __('Once a day'),
                BillingFrequencyUnit::WEEK => __('Once a week'),
                BillingFrequencyUnit::MONTH => __('Once a month'),
                BillingFrequencyUnit::YEAR => __('Once a year'),
            ];
        } else {
            $mapLabels = [
                BillingFrequencyUnit::DAY => __('Every %1 days', $frequency),
                BillingFrequencyUnit::WEEK => __('Every %1 weeks', $frequency),
                BillingFrequencyUnit::MONTH => __('Every %1 months', $frequency),
                BillingFrequencyUnit::YEAR => __('Every %1 years', $frequency),
            ];
        }

        return $mapLabels[$frequencyUnit] ?? __('Every %1 %2', $frequency, $frequencyUnit);
    }
}
