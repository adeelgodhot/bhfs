<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Config\Source;

use Amasty\RecurringPayments\Model\AbstractArray;

class Status extends AbstractArray
{
    const FAILED = 0;
    const SUCCESS = 1;
    const ACTION_REQUIRED = 2;

    public function toArray(): array
    {
        return [
            self::FAILED          => __('Failed'),
            self::SUCCESS         => __('Success'),
            self::ACTION_REQUIRED => __('Additional Action Required')
        ];
    }
}
