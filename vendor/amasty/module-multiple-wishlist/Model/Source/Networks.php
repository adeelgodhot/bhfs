<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Networks implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];
        foreach ($this->getArray() as $network) {
            $result[] = [
                'value' => $network['value'],
                'label' => $network['label']
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getArray(): array
    {
        return [
            [
                'value' => 'twitter',
                'label' => __('Twitter'),
                'is_template' => false,
                'url' => 'https://twitter.com/intent/tweet?text={title}&url={url}',
                'style' => 'background-position:-343px -55px;',
            ],
            [
                'value' => 'facebook',
                'label' => __('Facebook'),
                'is_template' => false,
                'url' => 'http://www.facebook.com/share.php?u={url}',
                'style' => 'background-position:-343px -1px;',
            ]
        ];
    }
}
