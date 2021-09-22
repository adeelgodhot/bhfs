<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\Processors;

use Amasty\RecurringPayments\Model\Amount;
use Magento\Quote\Api\Data\CartItemInterface;

class CreateCoupon extends AbstractProcessor
{
    /**
     * @param CartItemInterface $item
     * @param float $discountAmount
     * @return \Stripe\StripeObject|null
     */
    public function execute(CartItemInterface $item, float $discountAmount)
    {
        $params = [
            'duration'        => 'forever',
            'currency'        => $item->getQuote()->getBaseCurrencyCode(),
            'max_redemptions' => 1,
            'amount_off'      => $discountAmount * Amount::PERCENT
        ];

        return $this->adapter->couponCreate($params);
    }
}
