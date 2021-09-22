<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPart;

use Amasty\RecurringPayments\Api\Generators\TransactionGeneratorInterface;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;

class TransactionHandlerPart implements HandlerPartInterface
{

    /**
     * @var TransactionGeneratorInterface
     */
    private $transactionGenerator;

    public function __construct(TransactionGeneratorInterface $transactionGenerator)
    {
        $this->transactionGenerator = $transactionGenerator;
    }

    /**
     * @param HandleOrderContext $context
     * @return bool
     */
    public function handlePartial(HandleOrderContext $context): bool
    {
        if (!$context->getTransactionId()) {
            return true;
        }

        $transaction = $this->transactionGenerator->generate(
            $context->getOrder(),
            $context->getInvoice()
        );

        $context->setTransaction($transaction);

        return true;
    }

    /**
     * @param HandleOrderContext $context
     * @throws \InvalidArgumentException
     */
    public function validate(HandleOrderContext $context): void
    {
        if (!$context->getOrder()) {
            throw new \InvalidArgumentException('No order in context');
        }

        if (!$context->getInvoice()) {
            throw new \InvalidArgumentException('No invoice in context');
        }
    }
}
