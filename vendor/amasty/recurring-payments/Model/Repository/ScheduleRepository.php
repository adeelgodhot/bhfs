<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Repository;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule\Collection;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule\CollectionFactory;

class ScheduleRepository
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Schedule
     */
    private $scheduleResource;

    public function __construct(CollectionFactory $collectionFactory, Schedule $scheduleResource)
    {
        $this->collectionFactory = $collectionFactory;
        $this->scheduleResource = $scheduleResource;
    }

    /**
     * @param string $date
     * @return Collection
     */
    public function getListToExecute(string $date)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter(ScheduleInterface::STATUS, ScheduleInterface::STATUS_PENDING)
            ->addFieldToFilter(ScheduleInterface::SCHEDULED_AT, ['lteq' => $date])
            ->addOrder(ScheduleInterface::SCHEDULED_AT, Collection::SORT_ORDER_ASC);

        return $collection;
    }

    /**
     * @param ScheduleInterface $schedule
     * @param string $runningDate
     *
     * @return bool
     */
    public function tryToGetInProgress(ScheduleInterface $schedule, string $runningDate)
    {
        $result = $this->scheduleResource->tryToChangeStatus(
            (int) $schedule->getScheduleId(),
            ScheduleInterface::STATUS_PENDING,
            ScheduleInterface::STATUS_RUNNING,
            $runningDate
        );

        if ($result) {
            $schedule->setStatus(ScheduleInterface::STATUS_RUNNING);
            $schedule->setExecutedAt($runningDate);
        }

        return $result;
    }

    /**
     * @param ScheduleInterface $schedule
     * @param string $errorMessage
     * @param string $finishedDate
     */
    public function handleError(ScheduleInterface $schedule, string $errorMessage, string $finishedDate)
    {
        $schedule->setStatus(ScheduleInterface::STATUS_ERROR);
        $schedule->setMessage($errorMessage);
        $schedule->setFinishedAt($finishedDate);
        $this->scheduleResource->save($schedule);
    }

    /**
     * @param ScheduleInterface $schedule
     * @param string $finishedDate
     */
    public function handleSuccess(ScheduleInterface $schedule, string $finishedDate)
    {
        $schedule->setStatus(ScheduleInterface::STATUS_SUCCESS);
        $schedule->setFinishedAt($finishedDate);
        $this->scheduleResource->save($schedule);
    }

    /**
     * @param string $subscriptionId
     */
    public function massDeletePendingJobsBySubscriptionId(string $subscriptionId)
    {
        $this->scheduleResource->massDeletePendingJobsBySubscriptionId($subscriptionId);
    }
}
