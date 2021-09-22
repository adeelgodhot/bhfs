<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Generators;

use Amasty\RecurringPayments\Api\Data\TransactionInterface;

interface RecurringTransactionGeneratorInterface
{
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
    ): TransactionInterface;
}
