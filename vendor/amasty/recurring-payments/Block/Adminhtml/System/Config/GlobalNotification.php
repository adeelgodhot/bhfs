<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class GlobalNotification
 */
class GlobalNotification extends Field
{
    /**
     * @var string
     */
    const GLOBAL_COMMENT = "If you've just installed 'Subscriptions & Recurring Payments' extension or have never "
    . "configured your subscription products before, please take into consideration that, by default, products are "
    . "unavailable for subscription and might be properly configured. The most efficient way to turn your regular "
    . "products into subscription products is to configure global subscription settings and apply it to all your "
    . "products in bulk by going to <a href='%1' target='_blank'>Catalog</a> and executing 'Make Selected Products "
    . "Available via Subscription' action from the dropdown menu. Contrary, you can manually review your products and "
    . "configure custom subscription settings for them individually (this will override global configuration).";

    /**
     * @inheritdoc
     */
    public function render(AbstractElement $element)
    {
        $url = $this->getUrl('catalog/product/index');

        return '<div class="message message-notice notice">' . __(self::GLOBAL_COMMENT, $url) . '</div>';
    }
}
