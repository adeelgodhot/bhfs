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
 * Class BillingFrequencyUnit
 */
class BillingFrequencyUnit extends AbstractArray
{
    const DAY = 'day';
    const WEEK = 'week';
    const MONTH = 'month';
    const YEAR = 'year';

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $options = [
            self::DAY => __('Day'),
            self::WEEK => __('Week'),
            self::MONTH => __('Month'),
            self::YEAR => __('Year'),
        ];

        return $options;
    }
}
