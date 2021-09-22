<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model;

use Amasty\Followup\Model\Event\BasicFactory;
use Magento\Sales\Model\OrderRepository;

class Indexer extends \Magento\Framework\DataObject
{
    /**
     * @var ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var BasicFactory
     */
    protected $basicFactory;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    public function __construct(
        ScheduleFactory $scheduleFactory,
        BasicFactory $basicFactory,
        OrderRepository $orderRepository,
        array $data = []
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->basicFactory = $basicFactory;
        $this->orderRepository = $orderRepository;
        parent::__construct($data);
    }

    public function run($cronExecution = false)
    {
        $basic = $this->basicFactory->create();
        $basic->clear();
        $this->prepareOrderRules();
        $this->prepareCustomerRules();
        $this->scheduleFactory->create()->process();
        $basic->getFlag()->save();
    }

    public function prepareOrderRules()
    {
        $schedule = $this->scheduleFactory->create();
        $ruleCollection = $schedule->getRuleCollection(
            [
                Rule::TYPE_ORDER_NEW,
                Rule::TYPE_ORDER_SHIP,
                Rule::TYPE_ORDER_INVOICE,
                Rule::TYPE_ORDER_COMPLETE,
                Rule::TYPE_ORDER_CANCEL
            ]
        );

        foreach ($ruleCollection as $rule) {
            $event = $rule->getStartEvent();
            $quoteCollection = $event->getCollection();
            $quotes = $quoteCollection->getItems();

            foreach ($quotes as $quote) {
                if ($event->validate($quote)) {
                    $order = $this->orderRepository->get($quote->getOrderId());
                    $customer = $quote->getCustomer();
                    $schedule->createOrderHistory($rule, $event, $order, $quote, $customer);
                }
            }
        }
    }

    protected function prepareCustomerRules()
    {
        $schedule = $this->scheduleFactory->create();
        $ruleCollection = $schedule->getRuleCollection(
            [
                Rule::TYPE_CUSTOMER_ACTIVITY,
                Rule::TYPE_CUSTOMER_BIRTHDAY,
                Rule::TYPE_CUSTOMER_DATE,
                Rule::TYPE_CUSTOMER_WISHLIST,
            ]
        );

        foreach ($ruleCollection as $rule) {
            $event = $rule->getStartEvent();
            $customerCollection = $event->getCollection();

            foreach ($customerCollection as $customer) {
                if ($event->validate($customer)) {
                    $schedule->createCustomerHistory($rule, $event, $customer);
                }
            }
        }
    }
}
