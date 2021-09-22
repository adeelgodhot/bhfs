<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\Subscription\Processors;

use Amasty\RecurringPayments\Api\Subscription\CancelProcessorInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringStripe\Model\Adapter;

class Cancel implements CancelProcessorInterface
{
    /**
     * @var Adapter
     */
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param SubscriptionInterface $subscription
     */
    public function process(SubscriptionInterface $subscription): void
    {
        /** @var \Stripe\Subscription $stripeSubscription */
        $stripeSubscription = $this->adapter->subscriptionRetrieve($subscription->getSubscriptionId());
        $stripeSubscription->delete();
    }
}
