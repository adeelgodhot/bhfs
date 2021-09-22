<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */


namespace Amasty\Paction\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TierPrice implements OptionSourceInterface
{
    const VALUE_FIXED = 'fixed';
    const VALUE_PERCENT = 'percent';

    public function toOptionArray()
    {
        return [
            ['value' => self::VALUE_FIXED, 'label' => __('Fixed')],
            ['value' => self::VALUE_PERCENT, 'label' => __('Discount')],
        ];
    }
}
