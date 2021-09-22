<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Subscription\Scheduler\Handler;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Api\Processors\HandleSubscriptionInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class SubscriptionCharge extends AbstractScheduleHandler
{
    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var array
     */
    private $processors;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        RepositoryInterface $subscriptionRepository,
        DateTime $dateTime,
        array $processors = []
    ) {
        parent::__construct($subscriptionRepository);
        $this->subscriptionRepository = $subscriptionRepository;
        $this->processors = $processors;
        $this->dateTime = $dateTime;
    }

    /**
     * @param ScheduleInterface $schedule
     */
    public function handle(ScheduleInterface $schedule)
    {
        $subscription = $this->getSubscription($schedule->getSubscriptionId());
        $this->assertSubscriptionActive($subscription);
        $processor = $this->getProcessorForSubscription($subscription);
        $processor->process($subscription);
        $subscription->setLastPaymentDate($this->dateTime->gmtDate());
        $this->subscriptionRepository->save($subscription);
    }

    /**
     * @param SubscriptionInterface $subscription
     *
     * @return HandleSubscriptionInterface
     * @throws \RuntimeException
     */
    private function getProcessorForSubscription(SubscriptionInterface $subscription)
    {
        $processor = $this->processors[$subscription->getPaymentMethod()] ?? null;

        if ($processor === null) {
            throw new \RuntimeException('No processor found for charge ' . $subscription->getPaymentMethod());
        }

        return $processor;
    }
}
