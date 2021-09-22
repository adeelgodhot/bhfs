<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Generators;

use Magento\Sales\Api\Data\{InvoiceInterface, TransactionInterface, OrderInterface};

/**
 * Interface TransactionGeneratorInterface
 */
interface TransactionGeneratorInterface
{
    /**
     * @param OrderInterface $order
     * @param InvoiceInterface $invoice
     *
     * @return TransactionInterface
     */
    public function generate(OrderInterface $order, InvoiceInterface $invoice): TransactionInterface;
}
