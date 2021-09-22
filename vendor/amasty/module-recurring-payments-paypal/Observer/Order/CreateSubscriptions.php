<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


namespace Amasty\RecurringPaypal\Observer\Order;

use Amasty\RecurringPaypal\Model\Processor\Processor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class CreateSubscriptions implements ObserverInterface
{
    /**
     * @var Processor
     */
    private $orderProcessor;

    public function __construct(
        Processor $orderProcessor
    ) {
        $this->orderProcessor = $orderProcessor;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');
        $items = $observer->getData('subscription_items');

        if ($order instanceof OrderInterface && !empty($items)) {
            $this->orderProcessor->process($order, $items);
        }
    }
}
