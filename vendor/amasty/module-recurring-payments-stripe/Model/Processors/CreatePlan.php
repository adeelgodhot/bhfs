<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\Processors;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class CreatePlan extends AbstractProcessor
{
    /**
     * @param SubscriptionInterface $subscription
     * @param QuoteItem $item
     * @param string $productId
     * @return \Stripe\ApiResource
     */
    public function execute(
        SubscriptionInterface $subscription,
        QuoteItem $item,
        string $productId
    ): \Stripe\ApiResource {

        $params = [
            'product' => $productId,
            'currency' => $item->getQuote()->getBaseCurrencyCode(),
            'interval' => $subscription->getFrequencyUnit(),
            'interval_count' => $subscription->getFrequency(),
            'billing_scheme' => 'per_unit',
            'amount' => $subscription->getBaseGrandTotal() * \Amasty\RecurringPayments\Model\Amount::PERCENT
        ];

        return $this->adapter->planCreate($params);
    }
}
