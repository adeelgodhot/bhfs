<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Model\SalesRule;

use Amasty\RecurringPayments\Model\Product;
use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\OfflineShipping\Model\SalesRule\Calculator;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class CalculatorPlugin
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(Product $product, QuoteValidate $quoteValidate)
    {
        $this->product = $product;
        $this->quoteValidate = $quoteValidate;
    }

    /**
     * @param Calculator $calculator
     * @param Calculator $result
     * @param AbstractItem $item
     *
     * @return Calculator
     */
    public function afterProcessFreeShipping(Calculator $calculator, Calculator $result, AbstractItem $item)
    {
        /** @var Item $item */
        if (!$item->getFreeShipping()) {
            $buyRequest = $item->getBuyRequest();

            if (isset($buyRequest['subscription_product'])) {
                $item->setFreeShipping((int)$buyRequest['free_shipping']);
            } elseif ($this->quoteValidate->validateQuoteItem($item)) {
                $item->setFreeShipping((int)$this->product->isFreeShipping());
            }
        }

        return $result;
    }
}
