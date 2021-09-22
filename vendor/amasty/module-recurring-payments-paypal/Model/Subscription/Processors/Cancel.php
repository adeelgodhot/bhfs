<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\Subscription\Processors;

use Amasty\RecurringPayments\Api\Subscription\CancelProcessorInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPaypal\Model\Api\Adapter;
use Amasty\RecurringPaypal\Model\Subscription\Cache as SubscriptionCache;

class Cancel implements CancelProcessorInterface
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var SubscriptionCache
     */
    private $subscriptionCache;

    public function __construct(
        Adapter $adapter,
        SubscriptionCache $subscriptionCache
    ) {
        $this->adapter = $adapter;
        $this->subscriptionCache = $subscriptionCache;
    }

    /**
     * @param SubscriptionInterface $subscription
     */
    public function process(SubscriptionInterface $subscription): void
    {
        $this->adapter->cancelSubscription($subscription->getSubscriptionId(), (string)__('Cancelled by customer'));
        $this->subscriptionCache->clearSubscriptionData($subscription->getSubscriptionId());
    }
}
