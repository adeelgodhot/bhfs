<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Subscription\Scheduler\Handler;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Magento\Framework\Exception\LocalizedException;

abstract class AbstractScheduleHandler
{
    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;

    public function __construct(RepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @param ScheduleInterface $schedule
     */
    abstract public function handle(ScheduleInterface $schedule);

    /**
     * @param SubscriptionInterface $subscription
     * @throws LocalizedException
     */
    protected function assertSubscriptionActive(SubscriptionInterface $subscription)
    {
        if ($subscription->getStatus() != SubscriptionInterface::STATUS_ACTIVE) {
            throw new LocalizedException(__('Subscription not active'));
        }
    }

    /**
     * @param string $subscriptionId
     * @return SubscriptionInterface
     */
    protected function getSubscription(string $subscriptionId)
    {
        return $this->subscriptionRepository->getBySubscriptionId($subscriptionId);
    }
}
