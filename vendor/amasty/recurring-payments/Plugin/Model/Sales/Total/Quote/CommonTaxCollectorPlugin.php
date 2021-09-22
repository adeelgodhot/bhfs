<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Model\Sales\Total\Quote;

use Amasty\RecurringPayments\Model\TaxItemStorage;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

/**
 * Class CommonTaxCollectorPlugin
 */
class CommonTaxCollectorPlugin
{
    /**
     * @param CommonTaxCollector $subject
     * @param QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param AbstractItem $item
     * @param $priceIncludesTax
     * @param $useBaseCurrency
     * @param null $parentCode
     *
     * @return array
     */
    public function beforeMapItem(
        CommonTaxCollector $subject,
        QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode = null
    ): array {
        TaxItemStorage::$item = $item;

        return [$itemDataObjectFactory, $item, $priceIncludesTax, $useBaseCurrency, $parentCode];
    }

    /**
     * @param CommonTaxCollector $subject
     * @param QuoteDetailsItemInterface $result
     *
     * @return QuoteDetailsItemInterface
     */
    public function afterMapItem(
        CommonTaxCollector $subject,
        QuoteDetailsItemInterface $result
    ): QuoteDetailsItemInterface {
        TaxItemStorage::$item = null;

        return $result;
    }
}
