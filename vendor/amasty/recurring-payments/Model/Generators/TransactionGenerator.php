<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Generators;

use Amasty\RecurringPayments\Api\Generators\TransactionGeneratorInterface;
use Magento\Sales\Api\Data\{InvoiceInterface, OrderInterface, OrderPaymentInterface};
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;

/**
 * Class TransactionGenerator
 */
class TransactionGenerator implements TransactionGeneratorInterface
{
    /**
     * @var BuilderInterface
     */
    private $transactionBuilder;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(BuilderInterface $transactionBuilder, TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionBuilder = $transactionBuilder;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @inheritDoc
     */
    public function generate(OrderInterface $order, InvoiceInterface $invoice): TransactionInterface
    {
        /** @var OrderPaymentInterface $payment */
        $payment = $order->getData('payment');
        $transactionBuilder = $this->transactionBuilder->setPayment($payment);
        $transactionBuilder->setOrder($order);
        $transactionBuilder->setFailSafe(true);
        $transactionBuilder->setTransactionId($payment->getTransactionId());
        $transactionBuilder->setAdditionalInformation($payment->getTransactionAdditionalInfo());
        $transactionBuilder->setSalesDocument($invoice);
        /** @var TransactionInterface $transaction */
        $transaction = $transactionBuilder->build(Transaction::TYPE_CAPTURE);

        $this->transactionRepository->save($transaction);
        $payment->addTransactionCommentsToOrder($transaction, '');

        return $transaction;
    }
}
