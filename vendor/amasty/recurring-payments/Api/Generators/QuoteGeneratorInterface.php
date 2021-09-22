<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Generators;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Interface QuoteGeneratorInterface
 */
interface QuoteGeneratorInterface
{
    /**
     * @param SubscriptionInterface $subscription
     * @param \Magento\Sales\Api\Data\OrderAddressInterface|null $shippingAddress
     * @param \Magento\Sales\Api\Data\OrderAddressInterface|null $billingAddress
     * @return CartInterface
     */
    public function generate(
        SubscriptionInterface $subscription,
        ?\Magento\Sales\Api\Data\OrderAddressInterface $shippingAddress = null,
        ?\Magento\Sales\Api\Data\OrderAddressInterface $billingAddress = null
    ): CartInterface;

    /**
     * @param CartItemInterface $item
     * @return CartInterface
     */
    public function generateFromItem(CartItemInterface $item): CartInterface;
}
