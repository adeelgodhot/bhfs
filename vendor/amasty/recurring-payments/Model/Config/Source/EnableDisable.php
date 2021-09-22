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
 * Class EnableDisable
 */
class EnableDisable extends AbstractArray
{
    const DISABLE = 'disable';
    const ENABLE = 'enable';

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $options = [
            self::DISABLE => __('Disable'),
            self::ENABLE => __('Enable'),
        ];

        return $options;
    }
}
