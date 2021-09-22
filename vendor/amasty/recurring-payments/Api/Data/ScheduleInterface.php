<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Data;

interface ScheduleInterface
{
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    const JOB_CODE_SUBSCRIPTION_CHARGE = 'subscription_charge';
    const JOB_CODE_TRIAL_END = 'trial_end';
    const JOB_CODE_CANCEL_SUBSCRIPTION = 'cancel_subscription';

    const SCHEDULE_ID = 'schedule_id';
    const SUBSCRIPTION_ID = 'subscription_id';
    const JOB_CODE = 'job_code';
    const STATUS = 'status';
    const MESSAGE = 'message';
    const CREATED_AT = 'created_at';
    const SCHEDULED_AT = 'scheduled_at';
    const EXECUTED_AT = 'executed_at';
    const FINISHED_AT = 'finished_at';

    /**
     * @return int
     */
    public function getScheduleId();

    /**
     * @param int $scheduleId
     *
     * @return \Amasty\RecurringPayments\Api\Data\ScheduleInterface
     */
    public function setScheduleId($scheduleId);

    /**
     * @return string
     */
    public function getSubscriptionId();

    /**
     * @param string $subscriptionId
     *
     * @return \Amasty\RecurringPayments\Api\Data\ScheduleInterface
     */
    public function setSubscriptionId($subscriptionId);

    /**
     * @return string
     */
    public function getJobCode();

    /**
     * @param string $jobCode
     * @return \Amasty\RecurringPayments\Api\Data\ScheduleInterface
     */
    public function setJobCode($jobCode);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     *
     * @return \Amasty\RecurringPayments\Api\Data\ScheduleInterface
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getMessage();

    /**
     * @param string $message
     *
     * @return \Amasty\RecurringPayments\Api\Data\ScheduleInterface
     */
    public function setMessage($message);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return \Amasty\RecurringPayments\Api\Data\ScheduleInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string|null
     */
    public function getScheduledAt();

    /**
     * @param string|null $scheduledAt
     *
     * @return \Amasty\RecurringPayments\Api\Data\ScheduleInterface
     */
    public function setScheduledAt($scheduledAt);

    /**
     * @return string|null
     */
    public function getExecutedAt();

    /**
     * @param string|null $executedAt
     *
     * @return \Amasty\RecurringPayments\Api\Data\ScheduleInterface
     */
    public function setExecutedAt($executedAt);

    /**
     * @return string|null
     */
    public function getFinishedAt();

    /**
     * @param string|null $finishedAt
     *
     * @return \Amasty\RecurringPayments\Api\Data\ScheduleInterface
     */
    public function setFinishedAt($finishedAt);
}
