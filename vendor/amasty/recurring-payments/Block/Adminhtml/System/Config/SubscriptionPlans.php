<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SubscriptionPlans extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $url = $this->getUrl('amasty_recurring/plan/index');
        $element->setComment($this->getCommentMessage($url));

        return parent::render($element);
    }

    /**
     * @param string $url
     * @return \Magento\Framework\Phrase
     */
    private function getCommentMessage($url)
    {
        return __(
            "Select one or multiple plans your customers would be able to choose from when subscribing to products."
            . " If you need to add more plans or modify existing ones, please visit "
            . "<a href='%1' target='_blank'>Sales > Amasty Subscriptions > Subscription Plans</a>",
            $url
        );
    }
}
