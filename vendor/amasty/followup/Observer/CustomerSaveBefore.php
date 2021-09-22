<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Observer;

use Amasty\Followup\Model\Rule as Rule;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class CustomerSaveBefore implements ObserverInterface
{
    protected $scheduleFactory;
    protected $dateTime;
    protected $onCustomerChecked = false;

    public function __construct(
        \Amasty\Followup\Model\ScheduleFactory $scheduleFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->dateTime = $dateTime;
    }

    public function execute(EventObserver $observer)
    {
        $customer = $observer->getCustomer();

        if (!$this->onCustomerChecked) {
            if (!$customer->getCreatedAt()) {
                $customer->setData('created_at', $this->dateTime->formatDate(true));
            }

            $this->scheduleFactory->create()->checkCustomerRules(
                $customer,
                [
                    Rule::TYPE_CUSTOMER_GROUP,
                    Rule::TYPE_CUSTOMER_NEW
                ]
            );
            $this->onCustomerChecked = true;
        }
    }
}
