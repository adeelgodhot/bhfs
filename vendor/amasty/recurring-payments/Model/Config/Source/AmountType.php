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
 * Class AmountType
 */
class AmountType extends AbstractArray
{
    const FIXED_AMOUNT = 'fixed_amount';
    const PERCENT_AMOUNT = 'percent_product_price';

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $options = [
            self::FIXED_AMOUNT => __('Fixed Amount'),
            self::PERCENT_AMOUNT => __('Percent of Product Price'),
        ];

        return $options;
    }
}
