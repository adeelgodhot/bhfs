<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Generators;

use Magento\Sales\Api\Data\{InvoiceInterface, OrderInterface};

/**
 * Interface InvoiceGeneratorInterface
 */
interface InvoiceGeneratorInterface
{
    /**
     * @param OrderInterface $order
     * @param string|null $transactionId
     *
     * @return InvoiceInterface
     */
    public function generate(OrderInterface $order, string $transactionId = null): InvoiceInterface;
}
