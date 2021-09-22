<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Block\Customer;

use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\SubscriptionManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Subscriptions extends Template
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SubscriptionManagement
     */
    private $subscriptionManagement;

    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(
        Context $context,
        Session $session,
        SerializerInterface $serializer,
        SubscriptionManagement $subscriptionManagement,
        Config $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->session = $session;
        $this->serializer = $serializer;
        $this->subscriptionManagement = $subscriptionManagement;
        $this->configProvider = $configProvider;
    }

    /**
     * @return bool|string
     */
    public function getJsLayout()
    {
        $this->jsLayout['components'] = null;
        $customerId = $this->session->getCustomerId();

        $result = [
            'component' => 'Amasty_RecurringPayments/js/view/customer/subscriptions',
            'subscriptionsData' => $this->subscriptionManagement->getSubscriptions((int)$customerId),
            'cancelUrl' => $this->_urlBuilder->getUrl('amasty_recurring/customer/cancelSubscription'),
            'nextBillingDateTooltipEnabled' => $this->configProvider->isEnabledNextBillingDateTooltip(),
            'nextBillingDateTooltipText' => $this->configProvider->getNextBillingDateTooltipText()
        ];

        $this->jsLayout['components']['amasty-recurring-subscriptions'] = $result;

        return $this->serializer->serialize($this->jsLayout);
    }
}
