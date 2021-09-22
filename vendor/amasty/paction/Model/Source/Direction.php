<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Direction implements OptionSourceInterface
{
    const SELECTED_TO_IDS = 0;
    const IDS_TO_SELECTED = 1;

    public function toOptionArray()
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $result;
    }

    public function toArray(): array
    {
        return [
            self::SELECTED_TO_IDS => __('Selected to IDs'),
            self::IDS_TO_SELECTED => __('IDs to Selected')
        ];
    }
}
