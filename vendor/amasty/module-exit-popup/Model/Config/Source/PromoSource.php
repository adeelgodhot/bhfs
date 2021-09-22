<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PromoSource implements ArrayInterface
{
    const COUPON_CODE_VALUE = 0;
    const PRODUCT_VALUE = 1;

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::COUPON_CODE_VALUE, 'label' => __('Coupon Code')],
            ['value' => self::PRODUCT_VALUE, 'label' => __('Downloadable Product')],
        ];
    }
}
