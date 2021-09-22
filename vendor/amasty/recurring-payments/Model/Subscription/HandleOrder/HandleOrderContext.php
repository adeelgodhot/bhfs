<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\HandleOrder;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\TransactionInterface;

class HandleOrderContext extends DataObject
{
    /**
     * @var string|null
     */
    private $transactionId;

    /**
     * @var SubscriptionInterface|null
     */
    private $subscription;

    /**
     * @var CartInterface|null
     */
    private $quote;

    /**
     * @var OrderInterface|null
     */
    private $order;

    /**
     * @var InvoiceInterface|null
     */
    private $invoice;

    /**
     * @var TransactionInterface|null
     */
    private $transaction;

    /**
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId(string $transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return SubscriptionInterface|null
     */
    public function getSubscription(): ?SubscriptionInterface
    {
        return $this->subscription;
    }

    /**
     * @param SubscriptionInterface $subscription
     */
    public function setSubscription(SubscriptionInterface $subscription): void
    {
        $this->subscription = $subscription;
    }

    /**
     * @return CartInterface|null
     */
    public function getQuote(): ?CartInterface
    {
        return $this->quote;
    }

    /**
     * @param CartInterface $quote
     */
    public function setQuote(CartInterface $quote): void
    {
        $this->quote = $quote;
    }

    /**
     * @return OrderInterface|null
     */
    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    /**
     * @param OrderInterface $order
     */
    public function setOrder(OrderInterface $order): void
    {
        $this->order = $order;
    }

    /**
     * @return InvoiceInterface|null
     */
    public function getInvoice(): ?InvoiceInterface
    {
        return $this->invoice;
    }

    /**
     * @param InvoiceInterface $invoice
     */
    public function setInvoice(InvoiceInterface $invoice): void
    {
        $this->invoice = $invoice;
    }

    /**
     * @return TransactionInterface|null
     */
    public function getTransaction(): ?TransactionInterface
    {
        return $this->transaction;
    }

    /**
     * @param TransactionInterface $transaction
     */
    public function setTransaction(TransactionInterface $transaction): void
    {
        $this->transaction = $transaction;
    }
}
