<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Subscription\Scheduler;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Model\Repository\ScheduleRepository;
use Amasty\RecurringPayments\Model\Subscription\Scheduler\Handler\AbstractScheduleHandler;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Handler
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var AbstractScheduleHandler[]
     */
    private $handlers;

    /**
     * @var ScheduleRepository
     */
    private $scheduleRepository;

    public function __construct(
        ScheduleRepository $scheduleRepository,
        DateTime $dateTime,
        array $handlers = []
    ) {
        $this->scheduleRepository = $scheduleRepository;
        $this->dateTime = $dateTime;
        $this->handlers = $handlers;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function handle()
    {
        $collection = $this->scheduleRepository->getListToExecute($this->dateTime->gmtDate());

        /** @var \Amasty\RecurringPayments\Model\Schedule $schedule */
        foreach ($collection as $schedule) {
            if (!$this->scheduleRepository->tryToGetInProgress($schedule, $this->dateTime->gmtDate())) {
                continue;
            }

            try {
                $handler = $this->getHandler($schedule);
                $handler->handle($schedule);
            } catch (\Throwable $e) {
                $this->scheduleRepository->handleError(
                    $schedule,
                    (string)$e->getMessage(),
                    $this->dateTime->gmtDate()
                );
                continue;
            }

            $this->scheduleRepository->handleSuccess($schedule, $this->dateTime->gmtDate());
        }
    }

    /**
     * @param ScheduleInterface $schedule
     * @return AbstractScheduleHandler
     */
    private function getHandler(ScheduleInterface $schedule)
    {
        $handler = $this->handlers[$schedule->getJobCode()] ?? null;

        if ($handler === null) {
            throw new \RuntimeException('No handler found for ' . $schedule->getJobCode());
        }

        return $handler;
    }
}
