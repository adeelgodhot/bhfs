<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingCalculator
 */


namespace Amasty\ShippingCalculator\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ShippingCalculatorPlace implements ArrayInterface
{
    const ADDITIONAL_TAB = 'additional_tab';
    const AFTER_PRODUCT_DESCRIPTION = 'after_product_description';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => static::ADDITIONAL_TAB, 'label' => __('Product Page: In Additional Tab')],
            ['value' => static::AFTER_PRODUCT_DESCRIPTION, 'label' => __('Product Page: After Product Description')]
        ];
    }
}
