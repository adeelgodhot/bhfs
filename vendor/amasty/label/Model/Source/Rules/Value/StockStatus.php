<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Source\Rules\Value;

class StockStatus
{
    const IN_STOCK = 1;
    const OUT_OF_STOCK = 0;

    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('In Stock'),
                'value' => self::IN_STOCK
            ],
            [
                'label' => __('Out of Stock'),
                'value' => self::OUT_OF_STOCK
            ]
        ];
    }
}
