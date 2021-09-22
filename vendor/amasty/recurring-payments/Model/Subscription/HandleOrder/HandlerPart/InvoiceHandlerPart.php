<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPart;

use Amasty\RecurringPayments\Api\Generators\InvoiceGeneratorInterface;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;
use Magento\Sales\Api\InvoiceManagementInterface;

class InvoiceHandlerPart implements HandlerPartInterface
{

    /**
     * @var InvoiceGeneratorInterface
     */
    private $invoiceGenerator;

    /**
     * @var InvoiceManagementInterface
     */
    private $invoiceManagement;

    public function __construct(
        InvoiceGeneratorInterface $invoiceGenerator,
        InvoiceManagementInterface $invoiceManagement
    ) {
        $this->invoiceGenerator = $invoiceGenerator;
        $this->invoiceManagement = $invoiceManagement;
    }

    /**
     * @param HandleOrderContext $context
     * @return bool
     */
    public function handlePartial(HandleOrderContext $context): bool
    {
        $newInvoice = $this->invoiceGenerator->generate(
            $context->getOrder(),
            $context->getTransactionId()
        );

        $context->setInvoice($newInvoice);

        $this->invoiceManagement->notify($newInvoice->getEntityId());

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
