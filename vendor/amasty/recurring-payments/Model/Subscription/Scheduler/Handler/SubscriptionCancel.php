<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\Scheduler\Handler;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\Subscription\Operation\SubscriptionCancelOperation;

class SubscriptionCancel extends AbstractScheduleHandler
{
    /**
     * @var SubscriptionCancelOperation
     */
    private $subscriptionCancelOperation;

    public function __construct(
        RepositoryInterface $subscriptionRepository,
        SubscriptionCancelOperation $subscriptionCancelOperation
    ) {
        parent::__construct($subscriptionRepository);
        $this->subscriptionCancelOperation = $subscriptionCancelOperation;
    }

    /**
     * @param ScheduleInterface $schedule
     */
    public function handle(ScheduleInterface $schedule)
    {
        $subscription = $this->getSubscription($schedule->getSubscriptionId());
        $this->assertSubscriptionActive($subscription);
        $this->subscriptionCancelOperation->execute($subscription);
    }
}
