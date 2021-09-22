<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Quote;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;

class ItemComparer
{
    const DEFAULT_KEYS_FOR_COMPARE = [
        ProductRecurringAttributesInterface::START_DATE,
        ProductRecurringAttributesInterface::END_DATE,
        ProductRecurringAttributesInterface::TIMEZONE,
        ProductRecurringAttributesInterface::COUNT_CYCLES,
        ProductRecurringAttributesInterface::SUBSCRIPTION_PLAN_ID
    ];

    /**
     * @var array
     */
    private $dataForCompare;

    public function __construct(array $dataForCompare = [])
    {
        if (empty($dataForCompare)) {
            $dataForCompare = self::DEFAULT_KEYS_FOR_COMPARE;
        }
        $this->dataForCompare = $dataForCompare;
    }

    /**
     * @param array $itemBuyRequest
     * @param array $productBuyRequest
     * @return bool
     */
    public function compareWithProduct(array $itemBuyRequest, array $productBuyRequest)
    {
        $subscribeItem = $this->getSubscribe($itemBuyRequest);
        $subscribeProduct = $this->getSubscribe($productBuyRequest);

        if ($subscribeItem != $subscribeProduct) {
            return false;
        }

        if (!$subscribeItem) {
            return true;
        }

        foreach ($this->dataForCompare as $dataKey) {
            $valueA = $itemBuyRequest[$dataKey] ?? null;
            $valueB = $productBuyRequest[$dataKey] ?? null;

            if ($valueA != $valueB) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $buyRequest
     * @return bool
     */
    private function getSubscribe(array $buyRequest): bool
    {
        $subscribe = $buyRequest['subscribe'] ?? null;
        return $subscribe === 'subscribe'
            || isset($buyRequest['subscription_product']);
    }
}
