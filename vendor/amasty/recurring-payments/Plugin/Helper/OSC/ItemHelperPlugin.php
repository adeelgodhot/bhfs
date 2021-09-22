<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Plugin\Helper\OSC;

use Amasty\Checkout\Helper\Item;
use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;

class ItemHelperPlugin
{
    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(QuoteValidate $quoteValidate)
    {
        $this->quoteValidate = $quoteValidate;
    }

    /**
     * @param Item $subject
     * @param array $result
     * @param Quote $quote
     * @param QuoteItem|int $item
     * @param LayoutInterface $layout
     * @return array
     */
    public function afterGetItemOptionsConfig(Item $subject, $result, Quote $quote, $item, $layout)
    {
        $quoteItem = is_object($item) ? $item : $quote->getItemById($item);
        if ($this->quoteValidate->validateQuoteItem($quoteItem)) {
            $result['isEditable'] = false;
        }

        return $result;
    }
}
