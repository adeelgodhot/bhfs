<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Subscription\Scheduler\Handler;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\Subscription\Operation\TrialWillEndOperation;

class TrialWillEnd extends AbstractScheduleHandler
{
    /**
     * @var TrialWillEndOperation
     */
    private $trialWillEndOperation;

    public function __construct(
        RepositoryInterface $subscriptionRepository,
        TrialWillEndOperation $trialWillEndOperation
    ) {
        parent::__construct($subscriptionRepository);
        $this->trialWillEndOperation = $trialWillEndOperation;
    }

    /**
     * @param ScheduleInterface $schedule
     */
    public function handle(ScheduleInterface $schedule)
    {
        $subscription = $this->getSubscription($schedule->getSubscriptionId());
        $this->assertSubscriptionActive($subscription);
        $this->trialWillEndOperation->execute($subscription);
    }
}
