<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPart;

use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;

class PaymentDataHandlerPart implements HandlerPartInterface
{

    /**
     * @param HandleOrderContext $context
     * @return bool
     */
    public function handlePartial(HandleOrderContext $context): bool
    {
        // if transaction is empty - do nothing
        if (!$context->getTransactionId()) {
            return true;
        }

        $newOrder = $context->getOrder();

        $newOrder->getData('payment')->setTransactionId($context->getTransactionId());
        $newOrder->getData('payment')->setLastTransId($context->getTransactionId());

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

        if ($context->getTransactionId() === null) {
            throw new \InvalidArgumentException('No transaction id in context');
        }
    }
}
