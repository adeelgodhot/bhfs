<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace MGS\NinthTheme\Model\Config\Source;

class GridType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
		return [
			['value' => 1, 'label' => __('Grid Type 1')], 
			['value' => 2, 'label' => __('Grid Type 2')], 
			['value' => 3, 'label' => __('Grid Type 3')], 
			['value' => 4, 'label' => __('Grid Type 4')], 
			['value' => 5, 'label' => __('Grid Type 5')], 
			['value' => 7, 'label' => __('Grid Type 7')],
			['value' => 8, 'label' => __('Grid Type 8')], 
			['value' => 6, 'label' => __('Disable Hover Effect')], 
		];
    }
}
