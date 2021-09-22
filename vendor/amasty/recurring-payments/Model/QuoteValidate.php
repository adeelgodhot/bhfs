<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Model\Quote\ItemDataRetriever;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Validate Quote and Quote Items
 */
class QuoteValidate
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var ItemDataRetriever
     */
    private $itemDataRetriever;

    public function __construct(Product $product, ItemDataRetriever $itemDataRetriever)
    {
        $this->product = $product;
        $this->itemDataRetriever = $itemDataRetriever;
    }

    /**
     * @param CartInterface $quote
     *
     * @return bool
     */
    public function validateQuote(CartInterface $quote): bool
    {
        $isRecurring = false;
        $items = $quote->getAllItems();

        /** @var Item $item */
        foreach ($items as $item) {
            if ($this->validateQuoteItem($item)) {
                $isRecurring = true;
                break;
            }
        }

        return $isRecurring;
    }

    /**
     * @param AbstractItem $item
     *
     * @return bool
     */
    public function validateQuoteItem(AbstractItem $item): bool
    {
        return $this->itemDataRetriever->isSubscription($item) && $this->itemDataRetriever->getPlan($item);
    }
}
