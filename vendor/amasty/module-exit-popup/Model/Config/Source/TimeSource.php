<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class TimeSource implements ArrayInterface
{
    const FIFTEEN_MINUTES = 0;
    const THIRTY_MINUTES = 1;
    const ONE_HOUR = 2;
    const TWO_HOURS = 3;
    const FOUR_HOURS = 4;
    const EIGHT_HOURS = 5;
    const ONE_DAY = 6;
    const ONE_WEEK = 7;
    const CUSTOM = 8;

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::FIFTEEN_MINUTES, 'label' => __('15 minutes')],
            ['value' => self::THIRTY_MINUTES, 'label' => __('30 minutes')],
            ['value' => self::ONE_HOUR, 'label' => __('1 hour')],
            ['value' => self::TWO_HOURS, 'label' => __('2 hours')],
            ['value' => self::FOUR_HOURS, 'label' => __('4 hours')],
            ['value' => self::EIGHT_HOURS, 'label' => __('8 hours')],
            ['value' => self::ONE_DAY, 'label' => __('1 day')],
            ['value' => self::ONE_WEEK, 'label' => __('1 week')],
            ['value' => self::CUSTOM, 'label' => __('Custom')]
        ];
    }
}
