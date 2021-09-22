<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Generators;

use Amasty\RecurringPayments\Api\Data\TransactionInterface;
use Amasty\RecurringPayments\Api\Generators\RecurringTransactionGeneratorInterface;
use Amasty\RecurringPayments\Api\TransactionRepositoryInterface;
use Amasty\RecurringPayments\Model\TransactionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

class RecurringTransactionGenerator implements RecurringTransactionGeneratorInterface
{
    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var DateTime
     */
    private $date;

    public function __construct(
        TransactionFactory $transactionFactory,
        TransactionRepositoryInterface $transactionRepository,
        DateTime $date
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->transactionRepository = $transactionRepository;
        $this->date = $date;
    }

    /**
     * @param float $billingAmount
     * @param string $orderId
     * @param string $currency
     * @param string $transactionId
     * @param int $status
     * @param string $subscriptionId
     * @param string|null $billingDate
     * @return TransactionInterface
     */
    public function generate(
        float $billingAmount,
        string $orderId,
        string $currency,
        string $transactionId,
        int $status,
        string $subscriptionId,
        ?string $billingDate = null
    ): TransactionInterface {
        $billingDate === null && $billingDate = $this->date->gmtDate();

        /** @var TransactionInterface $transaction */
        $transaction = $this->transactionFactory->create();
        $transaction->setStatus($status);
        $transaction->setBillingAmount($billingAmount);
        $transaction->setBillingDate($billingDate);
        $transaction->setOrderId($orderId);
        $transaction->setBillingCurrencyCode($currency);
        $transaction->setTransactionId($transactionId);
        $transaction->setSubscriptionId($subscriptionId);

        $this->transactionRepository->save($transaction);

        return $transaction;
    }
}
