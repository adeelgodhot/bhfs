<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\IpnHandlers\Customer\Subscription;

use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Subscription\EmailNotifier;
use Amasty\RecurringPayments\Model\Subscription\Operation\TrialWillEndOperation;
use Amasty\RecurringStripe\Model\IpnHandlers\AbstractIpnHandler;

class TrialWillEnd extends AbstractIpnHandler
{
    /**
     * @var TrialWillEndOperation
     */
    private $trialWillEndOperation;

    public function __construct(
        Config $config,
        EmailNotifier $emailNotifier,
        RepositoryInterface $subscriptionRepository,
        TrialWillEndOperation $trialWillEndOperation
    ) {
        parent::__construct($config, $emailNotifier, $subscriptionRepository);
        $this->trialWillEndOperation = $trialWillEndOperation;
    }

    /**
     * @param \Stripe\Event $event
     */
    public function process(\Stripe\Event $event)
    {
        $subscription = $this->getSubscription($event);
        if (!$subscription || !$subscription->getTrialDays()) {
            return;
        }

        $this->trialWillEndOperation->execute($subscription);
    }
}
