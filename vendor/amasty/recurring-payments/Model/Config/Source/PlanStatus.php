<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Config\Source;

use Amasty\RecurringPayments\Model\AbstractArray;

class PlanStatus extends AbstractArray
{
    const SUSPENDED = 0;
    const ACTIVE = 1;

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::SUSPENDED => __('Suspended'),
            self::ACTIVE => __('Active'),
        ];
    }
}
