<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Config\Source;

use Amasty\RecurringPayments\Model\AbstractArray;

class TrialEnableDisable extends AbstractArray
{
    const DISABLE = 0;
    const ENABLE = 1;

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::DISABLE => __('Disable'),
            self::ENABLE => __('Enable'),
        ];
    }
}
