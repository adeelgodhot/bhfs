<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item;

class RequestProcessor
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
     * @param RateRequest $request
     * @param bool $isExistUsualProduct
     */
    public function process(RateRequest &$request, bool &$isExistUsualProduct)
    {
        $packageData = $this->calculatePackageData($request, $isExistUsualProduct);

        if ($isExistUsualProduct && $packageData['qty']) {
            $request->setFreeShipping(false);
            $request->setPackageWeight($packageData['weight']);
            $request->setPackageQty($packageData['qty']);
            $request->setPackageValue($packageData['value']);
            $request->setPackagePhysicalValue($packageData['value']);
            $request->setPackageValueWithDiscount($packageData['value_with_discount']);
            $request->setBaseSubtotalInclTax($packageData['subtotal_incl_tax']);
            $request->setAllItems($packageData['items']);
        }
    }

    /**
     * @param RateRequest $request
     * @param bool $isExistUsualProduct
     *
     * @return array
     */
    private function calculatePackageData(RateRequest $request, bool &$isExistUsualProduct): array
    {
        $itemKey = 0;
        $packageData = [
            'weight' => 0,
            'qty' => 0,
            'value' => 0,
            'value_with_discount' => 0,
            'subtotal_incl_tax' => 0,
            'items' => []
        ];

        /** @var Item $item */
        foreach ($request->getAllItems() as $item) {
            $buyRequest = $item->getBuyRequest();
            if (isset($buyRequest['subscription_product'])) {
                $isFreeShipping = !empty($buyRequest['free_shipping']);
            } else {
                $isFreeShipping = $this->quoteValidate->validateQuoteItem($item);
            }

            if (!$isFreeShipping) {
                $isExistUsualProduct = true;

                $packageData['weight'] += $item->getQty() * $item->getWeight();
                $packageData['qty'] += $item->getQty();
                $packageData['value'] += $item->getQty() * $item->getBaseCalculationPrice();
                $valueWithDiscount = $item->getBaseDiscountCalculationPrice()
                    ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
                $packageData['value_with_discount'] += $item->getQty() * $valueWithDiscount;
                $packageData['subtotal_incl_tax'] += $item->getBaseRowTotalInclTax();
                $packageData['items'][$itemKey] = $item;

                $itemKey++;
            }

            $item->getAddress()->setFreeShipping(false);
        }

        return $packageData;
    }
}
