<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TooltipStatus implements OptionSourceInterface
{
    const DISABLED = 1;
    const ENABLED_FOR_ALL_DEVICES = 2;
    const ENABLED_FOR_DESKTOP_ONLY = 3;

    public function toOptionArray(): array
    {
        return [
            [
                'label' => 'No',
                'value' => self::DISABLED
            ],
            [
                'label' => 'Yes for Both Desktop and Mobile',
                'value' => self::ENABLED_FOR_ALL_DEVICES
            ],
            [
                'label' => 'Yes for Desktop Only',
                'value' => self::ENABLED_FOR_DESKTOP_ONLY
            ]
        ];
    }
}
