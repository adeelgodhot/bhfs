<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Customer;

class Subscription extends \Amasty\Followup\Model\Event\Basic
{
    public function validateSubscription($subscriber, $customer)
    {
        $validateBasic = $this->_validateBasic(
            $customer->getStoreId(),
            $customer->getEmail(),
            $customer->getGroupId()
        );

        $validateSubscriber = in_array(
            $subscriber->getSubscriberStatus(),
            [
                \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE,
                \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED
            ]
        );

        return $validateBasic && $validateSubscriber;

    }
}
