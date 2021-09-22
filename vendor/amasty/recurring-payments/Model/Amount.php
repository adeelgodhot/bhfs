<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Model\Config\Source\AmountType;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class Amount
{
    const PERCENT = 100;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param MagentoProduct $product
     * @param float $amount
     * @param string $amountType
     * @param AbstractItem|null $item
     *
     * @return float
     */
    public function getAmount(
        MagentoProduct $product,
        float $amount,
        string $amountType,
        AbstractItem $item = null
    ): float {
        if ($amountType === AmountType::FIXED_AMOUNT) {
            if ($item) {
                $amount *= $item->getQty();
            }

            return (float)$amount;
        } else {
            return $this->getPercentAmount($product, (float)$amount, $item);
        }
    }

    /**
     * @param MagentoProduct $product
     * @param float $amount
     * @param AbstractItem|null $item
     *
     * @return float
     */
    public function getPercentAmount(MagentoProduct $product, float $amount, AbstractItem $item = null): float
    {
        if ($item) {
            $basePrice = $item->getBasePrice() ?: $item->getBaseOriginalPrice();
            if ($basePrice < 0.0001) {
                $basePrice = $product->getPriceModel()->getBasePrice($item->getProduct());
            }
        } else {
            $basePrice = $product->getPriceModel()->getBasePrice($product);
        }

        if ($item) {
            $amount = ($basePrice * $item->getQty()) / self::PERCENT * $amount;
        } else {
            $amount = ($basePrice) / self::PERCENT * $amount;
        }

        return (float)$amount;
    }

    /**
     * @param float $amount
     *
     * @return float
     */
    public function convert(float $amount): float
    {
        return $this->priceCurrency->convert($amount);
    }

    public function convertAndRound(float $amount): float
    {
        return $this->priceCurrency->convertAndRound($amount);
    }

    /**
     * @param float $amount
     *
     * @return string
     */
    public function convertAndFormat(float $amount): string
    {
        return $this->priceCurrency->convertAndFormat($amount, false);
    }
}
