<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule as ScheduleResource;
use Magento\Framework\Model\AbstractModel;

class Schedule extends AbstractModel implements ScheduleInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ScheduleResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getScheduleId()
    {
        return $this->_getData(ScheduleInterface::SCHEDULE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setScheduleId($scheduleId)
    {
        $this->setData(ScheduleInterface::SCHEDULE_ID, $scheduleId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubscriptionId()
    {
        return $this->_getData(ScheduleInterface::SUBSCRIPTION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->setData(ScheduleInterface::SUBSCRIPTION_ID, $subscriptionId);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getJobCode()
    {
        return $this->_getData(ScheduleInterface::JOB_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setJobCode($jobCode)
    {
        $this->setData(ScheduleInterface::JOB_CODE, $jobCode);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(ScheduleInterface::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->setData(ScheduleInterface::STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->_getData(ScheduleInterface::MESSAGE);
    }

    /**
     * @inheritdoc
     */
    public function setMessage($message)
    {
        $this->setData(ScheduleInterface::MESSAGE, $message);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->_getData(ScheduleInterface::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        $this->setData(ScheduleInterface::CREATED_AT, $createdAt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getScheduledAt()
    {
        return $this->_getData(ScheduleInterface::SCHEDULED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setScheduledAt($scheduledAt)
    {
        $this->setData(ScheduleInterface::SCHEDULED_AT, $scheduledAt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExecutedAt()
    {
        return $this->_getData(ScheduleInterface::EXECUTED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setExecutedAt($executedAt)
    {
        $this->setData(ScheduleInterface::EXECUTED_AT, $executedAt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFinishedAt()
    {
        return $this->_getData(ScheduleInterface::FINISHED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setFinishedAt($finishedAt)
    {
        $this->setData(ScheduleInterface::FINISHED_AT, $finishedAt);

        return $this;
    }
}
