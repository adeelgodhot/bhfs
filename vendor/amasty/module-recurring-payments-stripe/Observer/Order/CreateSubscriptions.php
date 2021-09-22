<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


namespace Amasty\RecurringStripe\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class CreateSubscriptions implements ObserverInterface
{
    /**
     * @var \Amasty\RecurringStripe\Model\Processor
     */
    private $orderProcessor;

    public function __construct(
        \Amasty\RecurringStripe\Model\Processor $orderProcessor
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
