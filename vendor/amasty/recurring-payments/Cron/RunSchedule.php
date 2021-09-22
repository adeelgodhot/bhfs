<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Cron;

use Amasty\RecurringPayments\Model\Subscription\Scheduler\Handler;

class RunSchedule
{

    /**
     * @var Handler
     */
    private $schedulerHandler;

    public function __construct(Handler $schedulerHandler)
    {
        $this->schedulerHandler = $schedulerHandler;
    }

    /**
     * Run subscriptions by schedule
     *
     * @return void
     */
    public function execute()
    {
        $this->schedulerHandler->handle();
    }
}
