<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Rule\Condition;

use Amasty\Label\Model\ConfigProvider;
use Amasty\Label\Model\Pricing\GetProductSpecialPrice;
use Amasty\Label\Model\Source\Rules\Operator\OnSale as OnSaleOptionSource;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class OnSale extends AbstractCondition
{
    /**
     * @var OnSaleOptionSource
     */
    private $onSaleOptionsProvider;

    /**
     * @var Yesno
     */
    private $yesNoOptionProvider;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetProductSpecialPrice
     */
    private $getProductSpecialPrice;

    public function __construct(
        Context $context,
        Yesno $yesNoOptionProvider,
        OnSaleOptionSource $onSaleOptionsProvider,
        TimezoneInterface $timezone,
        GetProductSpecialPrice $getProductSpecialPrice,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        $this->yesNoOptionProvider = $yesNoOptionProvider;
        $this->onSaleOptionsProvider = $onSaleOptionsProvider;
        $this->timezone = $timezone;
        $this->configProvider = $configProvider;

        parent::__construct(
            $context,
            $data
        );
        $this->getProductSpecialPrice = $getProductSpecialPrice;
    }

    public function collectValidatedAttributes(ProductCollection $collection): void
    {
        $collection->addPriceData();
        $collection->addAttributeToSelect('special_price');
        $collection->addAttributeToSelect('special_to_date');
        $collection->addAttributeToSelect('special_from_date');
    }

    public function validate(AbstractModel $model): bool
    {
        /** @var Product $model **/
        $isOnSale = $this->isProductOnSale($model);
        $operator = $this->getOperator();
        $configuredValue = $operator === OnSaleOptionSource::FOR_SPECIAL_PRICE_ONLY
            ? true : (bool) $this->getValue();

        return $operator === OnSaleOptionSource::FOR_SPECIAL_PRICE_ONLY || $operator === OnSaleOptionSource::EQUAL
            ? $isOnSale === $configuredValue : $isOnSale !== $configuredValue;
    }

    private function isProductOnSale(Product $product): bool
    {
        if (in_array($product->getTypeId(), ['giftcard', 'amgiftcard'])) {
            return false;
        }

        $specialPrice = $this->getSpecialPrice($product);
        $regularPrice = $product->getPriceInfo()->getPrice(RegularPrice::PRICE_CODE)->getAmount()->getValue();

        return $regularPrice > 0. && $specialPrice > 0.
            && $this->isPriceDiffGreaterThanConfiguredAbsoluteValue($specialPrice, $regularPrice)
            && $this->isPriceDiffGreaterThanConfiguredPercentValue($specialPrice, $regularPrice);
    }

    private function isPriceDiffGreaterThanConfiguredAbsoluteValue(float $specialPrice, float $regularPrice): bool
    {
        $priceDiff = $regularPrice - $specialPrice;
        $minDiff = $this->configProvider->getMinDiscountAbsolute();

        return $priceDiff > 0.001 && ($minDiff === 0. || $priceDiff >= $minDiff);
    }

    private function isPriceDiffGreaterThanConfiguredPercentValue(float $specialPrice, float $regularPrice): bool
    {
        $priceDiff = $regularPrice - $specialPrice;
        $percentDiff = ceil($priceDiff * 100 / $regularPrice);
        $minPercent = $this->configProvider->getMinDiscountPercentage();

        return $percentDiff > 0.001 && ($minPercent === 0. || $percentDiff >= $minPercent);
    }

    /**
     * @return array|Phrase|null
     */
    public function getOperatorName()
    {
        $options = $this->onSaleOptionsProvider->toArray();

        return $options[$this->getOperator()] ?? null;
    }

    private function getSpecialPrice(Product $product): float
    {
        $isSpecialPriceOnly = $this->getOperator() === OnSaleOptionSource::FOR_SPECIAL_PRICE_ONLY;

        if ($isSpecialPriceOnly) {
            $specialPrice = $this->getProductSpecialPrice->execute($product);
        } else {
            $specialPrice = $product->getPriceInfo()
                ->getPrice(FinalPrice::PRICE_CODE)->getAmount()->getValue();
        }

        return (float) $specialPrice;
    }

    public function getAttributeElementHtml(): Phrase
    {
        return __('Sale');
    }

    public function getInputType(): string
    {
        return 'select';
    }

    public function getValueElementType(): string
    {
        return 'select';
    }

    public function getOperatorSelectOptions(): array
    {
        return $this->onSaleOptionsProvider->toOptionArray();
    }

    public function getValueSelectOptions(): array
    {
        return $this->yesNoOptionProvider->toOptionArray();
    }
}
