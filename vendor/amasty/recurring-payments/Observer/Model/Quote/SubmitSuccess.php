<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Observer\Model\Quote;

use Amasty\RecurringPayments\Model\Payment;
use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Item;

class SubmitSuccess implements ObserverInterface
{
    /**
     * @var Payment
     */
    private $payment;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    public function __construct(
        Payment $payment,
        CartRepositoryInterface $quoteRepository,
        QuoteValidate $quoteValidate,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->payment = $payment;
        $this->quoteRepository = $quoteRepository;
        $this->quoteValidate = $quoteValidate;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getData('order');
        /** @var \Magento\Sales\Api\Data\OrderPaymentInterface $payment */
        $payment = $order->getData('payment');

        if (!in_array($payment->getMethod(), $this->payment->getSupportedMethods())) {
            return;
        }

        $quoteItems = $this->quoteRepository->get($order->getQuoteId())->getAllVisibleItems();

        if (!$quoteItems) {
            return;
        }

        $subscriptionItems = [];
        /** @var Item $item */
        foreach ($quoteItems as $item) {
            if ($this->quoteValidate->validateQuoteItem($item)) {
                $subscriptionItems[] = $item;
            }
        }

        if ($subscriptionItems) {
            $args = [
                'order'              => $order,
                'subscription_items' => $subscriptionItems
            ];
            $this->eventManager->dispatch('amasty_recurring_order_placed_' . $payment->getMethod(), $args);
        }
    }
}
