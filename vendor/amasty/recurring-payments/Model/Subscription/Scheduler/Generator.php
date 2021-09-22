<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\Scheduler;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Payment;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule\Subscription\Collection;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule\Subscription\CollectionFactory;

class Generator
{
    const SCHEDULE_LIMIT = 5;

    /**
     * @var CollectionFactory
     */
    private $subscriptionCollectionFactory;

    /**
     * @var Payment
     */
    private $paymentConfig;

    /**
     * @var Schedule
     */
    private $scheduleResource;

    /**
     * @var DateTimeInterval
     */
    private $dateTimeInterval;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        CollectionFactory $subscriptionCollectionFactory,
        Payment $paymentConfig,
        Schedule $scheduleResource,
        DateTimeInterval $dateTimeInterval,
        Config $config
    ) {
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->paymentConfig = $paymentConfig;
        $this->scheduleResource = $scheduleResource;
        $this->dateTimeInterval = $dateTimeInterval;
        $this->config = $config;
    }

    /**
     * Generate schedule for subscriptions
     */
    public function generate()
    {
        $supportedMethods = $this->paymentConfig->getCronHandledMethods();
        if (!$supportedMethods) {
            return;
        }
        /** @var Collection $collection */
        $collection = $this->subscriptionCollectionFactory->create();
        $collection->addScheduleFilter(self::SCHEDULE_LIMIT);
        $collection->addFieldToFilter(SubscriptionInterface::PAYMENT_METHOD, $supportedMethods);

        foreach ($collection as $subscription) {
            $this->generateScheduleForSubscription($subscription);
        }
    }

    /**
     * @param SubscriptionInterface $subscription
     */
    private function generateScheduleForSubscription(SubscriptionInterface $subscription)
    {
        $date = $subscription->getData('last_scheduled_date');
        $interval = $subscription->getFrequency();
        $intervalUnits = $subscription->getFrequencyUnit();

        !$date && $date = $subscription->getLastPaymentDate();

        $excludeStartDate = true;
        if (!$date) {
            $date = $subscription->getStartDate();
            if ($subscription->getTrialDays() > 0) {
                $excludeStartDate = false;
                $date = $this->dateTimeInterval->getStartDateAfterTrial(
                    $subscription->getStartDate(),
                    $subscription->getTrialDays()
                );
                $this->generateTrialEndNotification($subscription);
            }

            if ($subscription->getEndDate()) {
                $this->generateCancelSubscriptionSchedule($subscription);
            }
        }

        $listPeriods = $this->dateTimeInterval->getDateRangeForSubscription(
            $date,
            $interval,
            $intervalUnits,
            self::SCHEDULE_LIMIT,
            $excludeStartDate
        );

        if ($subscription->getEndDate()) {
            $newListPeriods = [];
            foreach ($listPeriods as $key => $period) {
                $periodDateObject = new \DateTime($period);
                $endDateObject = new \DateTime($subscription->getEndDate());
                if ($periodDateObject > $endDateObject) {
                    break;
                }
                $newListPeriods[] = $period;
            }

            $listPeriods = $newListPeriods;
        }

        if (count($listPeriods)) {
            $this->scheduleResource->massCreateSchedule($subscription->getSubscriptionId(), $listPeriods);
        }
    }

    /**
     * @param SubscriptionInterface $subscription
     */
    private function generateTrialEndNotification(SubscriptionInterface $subscription)
    {
        $storeId = (int)$subscription->getStoreId();
        if (!$subscription->getTrialDays() || !$this->config->isNotifyTrialEnd($storeId)) {
            return;
        }
        $days = $this->config->getTrialEndDaysBeforeNotification($storeId);
        $daysBeforeNotification = $subscription->getTrialDays() - $days;
        $daysBeforeNotification < 0 && $daysBeforeNotification = 0;
        $date = $this->dateTimeInterval->getStartDateAfterTrial(
            $subscription->getStartDate(),
            $daysBeforeNotification
        );

        $this->scheduleResource->createTrialEndNotificationSchedule($subscription->getSubscriptionId(), $date);
    }

    /**
     * @param SubscriptionInterface $subscription
     */
    private function generateCancelSubscriptionSchedule(SubscriptionInterface $subscription)
    {
        $cancelDate = $this->dateTimeInterval->getNextBillingDate(
            $subscription->getEndDate(),
            $subscription->getFrequency(),
            $subscription->getFrequencyUnit()
        );
        $this->scheduleResource->createCancelSchedule($subscription->getSubscriptionId(), $cancelDate);
    }
}
