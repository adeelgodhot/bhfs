<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\IpnHandlers\Invoice;

use Amasty\RecurringPayments\Api\Data\TransactionInterface;
use Amasty\RecurringPayments\Api\Generators\RecurringTransactionGeneratorInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\Subscription\EmailNotifier;
use Amasty\RecurringPayments\Api\TransactionRepositoryInterface;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Date;
use Amasty\RecurringStripe\Model\IpnHandlers\AbstractIpnHandler;
use Amasty\RecurringPayments\Model\TransactionFactory;

abstract class AbstractInvoice extends AbstractIpnHandler
{
    /**
     * @var Date
     */
    private $date;

    /**
     * @var RecurringTransactionGeneratorInterface
     */
    private $recurringTransactionGenerator;

    public function __construct(
        Config $config,
        EmailNotifier $emailNotifier,
        RepositoryInterface $subscriptionRepository,
        Date $date,
        RecurringTransactionGeneratorInterface $recurringTransactionGenerator
    ) {
        parent::__construct(
            $config,
            $emailNotifier,
            $subscriptionRepository
        );
        $this->date = $date;
        $this->recurringTransactionGenerator = $recurringTransactionGenerator;
    }

    /**
     * @param \Stripe\Event $event
     * @param int $status
     */
    protected function saveTransactionLog(\Stripe\Event $event, int $status)
    {
        /** @var \Stripe\Invoice $invoice */
        $invoice = $event->data->object;
        $transactionDate = $invoice->status_transitions->paid_at ?? $invoice->created;

        $this->recurringTransactionGenerator->generate(
            $invoice->amount_due / \Amasty\RecurringPayments\Model\Amount::PERCENT,
            $this->getOrderId($invoice),
            mb_strtoupper($invoice->currency),
            $invoice->charge ?? $invoice->id,
            $status,
            (string)$invoice->subscription,
            $this->date->convertFromUnix((int)$transactionDate)
        );
    }

    /**
     * @param \Stripe\Invoice $invoice
     *
     * @return string
     */
    private function getOrderId(\Stripe\Invoice $invoice):string
    {
        $items = $invoice->lines->data;
        $item = $items[0] ?? null;
        $orderId = null;

        if ($item) {
            /** @var \Stripe\StripeObject $metadata */
            $metadata = $item['metadata'] ?? null;

            if ($metadata) {
                $orderId = $metadata['increment_id'] ?? null;
            }
        }

        return (string)$orderId;
    }
}
