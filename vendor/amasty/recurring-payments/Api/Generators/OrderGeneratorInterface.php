<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Generators;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface OrderGeneratorInterface
 */
interface OrderGeneratorInterface
{
    /**
     * @param CartInterface $quote
     * @param string $paymentMethod
     * @param string $currency
     *
     * @return OrderInterface
     */
    public function generate(
        CartInterface $quote,
        string $paymentMethod,
        string $currency
    ): OrderInterface;
}
