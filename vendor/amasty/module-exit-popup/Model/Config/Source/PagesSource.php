<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PagesSource implements OptionSourceInterface
{
    const CART_VALUE = 0;
    const CHECKOUT_VALUE = 1;
    const PAYPAL_VALUE = 2;

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CART_VALUE, 'label' => __('Shopping Cart')],
            ['value' => self::CHECKOUT_VALUE, 'label' => __('Checkout')],
            ['value' => self::PAYPAL_VALUE, 'label' => __('PayPal Express Checkout (Review Page)')]
        ];
    }
}
