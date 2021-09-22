<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringCashOnDelivery
 */


namespace Amasty\RecurringCashOnDelivery\Observer\Order;

use Amasty\RecurringCashOnDelivery\Model\Processor\OrderProcessor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class CreateSubscriptions implements ObserverInterface
{
    /**
     * @var OrderProcessor
     */
    private $orderProcessor;

    public function __construct(
        OrderProcessor $orderProcessor
    ) {
        $this->orderProcessor = $orderProcessor;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');
        $items = $observer->getData('subscription_items');

        if ($order instanceof OrderInterface && !empty($items)) {
            $this->orderProcessor->process($order, $items);
        }
    }
}
