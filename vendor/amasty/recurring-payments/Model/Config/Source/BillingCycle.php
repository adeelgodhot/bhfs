<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Config\Source;

use Amasty\RecurringPayments\Model\AbstractArray;

/**
 * @deprecated since 1.6.0
 */
class BillingCycle extends AbstractArray
{
    const CUSTOM = 'custom';
    const ONCE_DAY = 'once_day';
    const ONCE_WEEK = 'once_week';
    const ONCE_MONTH = 'once_month';
    const ONCE_YEAR = 'once_year';
    const CUSTOMER_DECIDE = 'customer_decide';

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $options = [
            self::CUSTOM => __('Custom'),
            self::ONCE_DAY => __('Once a day'),
            self::ONCE_WEEK => __('Once a week'),
            self::ONCE_MONTH => __('Once a month'),
            self::ONCE_YEAR => __('Once a year'),
            self::CUSTOMER_DECIDE => __('Let your customer decide'),
        ];

        return $options;
    }
}
