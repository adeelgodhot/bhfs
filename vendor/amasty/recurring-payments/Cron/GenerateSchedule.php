<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Cron;

use Amasty\RecurringPayments\Model\Subscription\Scheduler\Generator;

class GenerateSchedule
{
    /**
     * @var Generator
     */
    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Generate schedule for subscriptions
     *
     * @return void
     */
    public function execute()
    {
        $this->generator->generate();
    }
}
