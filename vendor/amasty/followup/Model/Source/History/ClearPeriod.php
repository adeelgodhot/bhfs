<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Source\History;

class ClearPeriod implements \Magento\Framework\Option\ArrayInterface
{
    const CLEAR_HISTORY_CONFIG_PATH = 'amfollowup/general/clear_history';
    const NO = 0;
    const THIRTY_DAYS = 30;
    const NINETY_DAYS = 90;
    const ONE_HUNDRED_EIGHTY_DAYS = 180;
    const THREE_HUNDRED_SIXTY_DAYS = 360;

    public function toOptionArray()
    {
        return [
            ['value' => self::NO, 'label' => __('No')],
            ['value' => self::THIRTY_DAYS, 'label' => __('In 30 days')],
            ['value' => self::NINETY_DAYS, 'label' => __('In 90 days')],
            ['value' => self::ONE_HUNDRED_EIGHTY_DAYS, 'label' => __('In 180 days')],
            ['value' => self::THREE_HUNDRED_SIXTY_DAYS, 'label' => __('In 360 days')]
        ];
    }
}
