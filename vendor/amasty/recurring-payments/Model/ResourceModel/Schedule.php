<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\ResourceModel;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Schedule extends AbstractDb
{
    const TABLE_NAME = 'amasty_recurring_payments_schedule';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ScheduleInterface::SCHEDULE_ID);
    }

    /**
     * @param string $subscriptionId
     * @param array $dates
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function massCreateSchedule(string $subscriptionId, array $dates)
    {
        $insertData = [];

        foreach ($dates as $date) {
            $insertData[] = [
                ScheduleInterface::SUBSCRIPTION_ID => $subscriptionId,
                ScheduleInterface::SCHEDULED_AT => $date,
                ScheduleInterface::JOB_CODE => ScheduleInterface::JOB_CODE_SUBSCRIPTION_CHARGE
            ];
        }

        $connection = $this->getConnection();
        $connection->insertMultiple($this->getMainTable(), $insertData);
    }

    /**
     * @param string $subscriptionId
     * @param string $date
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createTrialEndNotificationSchedule(string $subscriptionId, string $date)
    {
        $insertData = [
            ScheduleInterface::SUBSCRIPTION_ID => $subscriptionId,
            ScheduleInterface::SCHEDULED_AT => $date,
            ScheduleInterface::JOB_CODE => ScheduleInterface::JOB_CODE_TRIAL_END
        ];

        $connection = $this->getConnection();
        $connection->insert($this->getMainTable(), $insertData);
    }

    /**
     * @param string $subscriptionId
     * @param string $date
     */
    public function createCancelSchedule(string $subscriptionId, string $date)
    {
        $insertData = [
            ScheduleInterface::SUBSCRIPTION_ID => $subscriptionId,
            ScheduleInterface::SCHEDULED_AT => $date,
            ScheduleInterface::JOB_CODE => ScheduleInterface::JOB_CODE_CANCEL_SUBSCRIPTION
        ];

        $connection = $this->getConnection();
        $connection->insert($this->getMainTable(), $insertData);
    }

    /**
     * @param int $scheduleId
     * @param string $currentStatus
     * @param string $newStatus
     * @param string $runningDate
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function tryToChangeStatus(
        int $scheduleId,
        string $currentStatus,
        string $newStatus,
        string $runningDate
    ): bool {
        $connection = $this->getConnection();
        $result = $connection->update(
            $this->getMainTable(),
            [ScheduleInterface::STATUS => $newStatus, ScheduleInterface::EXECUTED_AT => $runningDate],
            [
                ScheduleInterface::SCHEDULE_ID . ' = ?' => $scheduleId,
                ScheduleInterface::STATUS . ' = ?' => $currentStatus
            ]
        );
        if ($result == 1) {
            return true;
        }

        return false;
    }

    /**
     * @param string $subscriptionId
     * @return int
     */
    public function massDeletePendingJobsBySubscriptionId(string $subscriptionId)
    {
        $connection = $this->getConnection();

        return $connection->delete(
            $this->getMainTable(),
            [
                ScheduleInterface::SUBSCRIPTION_ID . ' = ?' => $subscriptionId,
                ScheduleInterface::STATUS . ' = ?' => ScheduleInterface::STATUS_PENDING
            ]
        );
    }
}
