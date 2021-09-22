<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ExitPopup
 */


namespace Amasty\ExitPopup\Model;

use Magento\Newsletter\Model\Subscriber;
use Amasty\ExitPopup\Model\Validate;
use Magento\Newsletter\Model\SubscriberFactory;

class Subscription
{
    /**
     * @var Validate
     */
    private $validate;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    public function __construct(
        Validate $validate,
        SubscriberFactory $subscriberFactory
    ) {
        $this->validate = $validate;
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * @param string $email
     *
     * @throws \Exception
     */
    public function subscribeCustomer($email)
    {
        if (
            $this->validate->validateEmailFormat($email)
            && $this->validate->validateGuestSubscription()
            && $this->validate->validateEmailAvailable($email)
        ) {
            /** @var Subscriber $subscriber */
            $subscriber = $this->subscriberFactory->create()->loadByEmail($email);

            if ((int)$subscriber->getSubscriberStatus() !== Subscriber::STATUS_SUBSCRIBED) {
                $this->subscriberFactory->create()->subscribe($email);
            }
        }
    }
}
