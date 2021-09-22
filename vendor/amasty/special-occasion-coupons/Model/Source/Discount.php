<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Birth
 */
namespace Amasty\Birth\Model\Source;

class Discount implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => 'by_percent',
                'label' => __('Percent of product price discount')
            ],
            [
                'value' => 'by_fixed',
                'label' => __('Fixed amount discount')
            ],
            [
                'value' => 'cart_fixed',
                'label' => __('Fixed amount discount for whole cart')
            ]
        ];

        return $options;
    }
}
