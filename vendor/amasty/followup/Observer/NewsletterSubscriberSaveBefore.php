<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class NewsletterSubscriberSaveBefore implements ObserverInterface
{

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if (!$observer->getSubscriber()->getId()) {
            \Amasty\Followup\Observer\NewsletterSubscriber::$isFirstSubscribe = true;
        } else {
            \Amasty\Followup\Observer\NewsletterSubscriber::$isFirstSubscribe = false;
        }
    }
}
