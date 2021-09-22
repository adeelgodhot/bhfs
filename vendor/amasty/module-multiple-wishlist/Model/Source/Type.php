<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source for type of one list.
 *
 * Class Type
 */
class Type implements OptionSourceInterface
{
    const WISH = 0;
    const REQUISITION = 1;

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::WISH,
                'label' => __('Wish')
            ],
            [
                'value' => self::REQUISITION,
                'label' => __('Requisition')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::WISH => __('Wish'),
            self::REQUISITION => __('Requisition')
        ];
    }

    /**
     * @param int $type
     *
     * @return string
     */
    public function getTypeLabel(int $type): string
    {
        $label = '';
        foreach ($this->toOptionArray() as $option) {
            if ($option['value'] === $type) {
                $label = $option['label']->render();
                break;
            }
        }

        return $label;
    }
}
