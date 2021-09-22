<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\ResourceModel\Schedule\Subscription;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule;

class Collection extends \Amasty\RecurringPayments\Model\ResourceModel\Subscription\Collection
{
    /**
     * @param int $scheduleLimit
     */
    public function addScheduleFilter(int $scheduleLimit)
    {
        $select = $this->getSelect();
        $scheduleTable = $this->getTable(Schedule::TABLE_NAME);
        $select->joinLeft(
            ['schedule' => $scheduleTable],
            'schedule.subscription_id = main_table.subscription_id AND '.
            'schedule.status = :status_pending AND schedule.job_code = :job_code_subscription',
            [
                'count_schedule' => new \Zend_Db_Expr('COUNT(schedule.schedule_id)'),
                'last_scheduled_date' => new \Zend_Db_Expr('MAX(schedule.' . ScheduleInterface::SCHEDULED_AT . ')')
            ]
        );

        $select->group('main_table.id');
        $select->having('count_schedule < ?', $scheduleLimit);
        $select->where('main_table.status = ?', SubscriptionInterface::STATUS_ACTIVE);

        $this->addBindParam(':status_pending', ScheduleInterface::STATUS_PENDING);
        $this->addBindParam(':job_code_subscription', ScheduleInterface::JOB_CODE_SUBSCRIPTION_CHARGE);
    }
}
