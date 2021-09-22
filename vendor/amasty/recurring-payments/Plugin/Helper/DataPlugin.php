<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Helper;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Amasty\RecurringPayments\Model\TaxItemStorage;
use Magento\Tax\Helper\Data;

class DataPlugin
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
     * @param Data $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterApplyTaxOnOriginalPrice(Data $subject, bool $result): bool
    {
        // @TODO: refactor storage
        $item = TaxItemStorage::$item;
        if (!$result && $item) {
            $buyRequest = $item->getBuyRequest();

            if (isset($buyRequest['subscription_product']) || $this->quoteValidate->validateQuoteItem($item)) {
                $result = true;
            }
        }

        return $result;
    }
}
