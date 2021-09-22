<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Text\Processors;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Exceptions\TextRenderException;
use Amasty\Label\Model\ConfigProvider;
use Amasty\Label\Model\Label\Text\ProcessorInterface;
use Amasty\Label\Model\Pricing\GetProductSpecialPrice;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Data as CatalogData;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\StoreManagerInterface;

class PricesProcessor implements ProcessorInterface
{
    const SPECIAL_PRICE = 'SPECIAL_PRICE';
    const PRICE = 'PRICE';
    const FINAL_PRICE = 'FINAL_PRICE';
    const FINAL_PRICE_INCL_TAX = 'FINAL_PRICE_INCL_TAX';
    const STARTINGFROM_PRICE = 'STARTINGFROM_PRICE';
    const STARTINGTO_PRICE = 'STARTINGTO_PRICE';
    const SAVE_AMOUNT = 'SAVE_AMOUNT';
    const SAVE_PERCENT = 'SAVE_PERCENT';

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var GetProductSpecialPrice
     */
    private $getProductSpecialPrice;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CatalogData
     */
    private $catalogData;

    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        GetProductSpecialPrice $getProductSpecialPrice,
        StoreManagerInterface $storeManager,
        CatalogData $catalogData,
        Configurable $configurableType,
        ConfigProvider $configProvider
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->getProductSpecialPrice = $getProductSpecialPrice;
        $this->storeManager = $storeManager;
        $this->catalogData = $catalogData;
        $this->configurableType = $configurableType;
        $this->configProvider = $configProvider;
    }

    public function getAcceptableVariables(): array
    {
        return [
            self::PRICE,
            self::SPECIAL_PRICE,
            self::FINAL_PRICE,
            self::FINAL_PRICE_INCL_TAX,
            self::STARTINGFROM_PRICE,
            self::STARTINGTO_PRICE,
            self::SAVE_AMOUNT,
            self::SAVE_PERCENT
        ];
    }

    /**
     * @param string $variable
     * @param LabelInterface $label
     * @param ProductInterface $product
     * @return string
     *
     * @throws TextRenderException
     */
    public function getVariableValue(
        string $variable,
        LabelInterface $label,
        ProductInterface $product
    ): string {
        $prices = $this->calculatePricesForLabel($label);

        switch ($variable) {
            case self::PRICE:
                $result = $this->convertPrice($prices[self::PRICE]);
                break;
            case self::SPECIAL_PRICE:
                $result = $this->convertPrice($prices[self::SPECIAL_PRICE]);
                break;
            case self::FINAL_PRICE:
                $result = $this->convertPrice(
                    $this->catalogData->getTaxPrice($product, $product->getFinalPrice(), false)
                );
                break;
            case self::FINAL_PRICE_INCL_TAX:
                $result = $this->convertPrice(
                    $this->catalogData->getTaxPrice($product, $product->getFinalPrice(), true)
                );
                break;
            case self::STARTINGFROM_PRICE:
                $result = $this->convertPrice($this->getMinimalPrice($product));
                break;
            case self::STARTINGTO_PRICE:
                $result = $this->convertPrice($this->getMaximalPrice($product));
                break;
            case self::SAVE_AMOUNT:
                $result = $this->convertPrice($prices[self::PRICE] - $prices[self::SPECIAL_PRICE]);
                break;
            case self::SAVE_PERCENT:
                $result = $this->calculateSavePercentage($prices);
                break;
            default:
                throw new TextRenderException(__('The passed variable could not be processed'));
        }

        return (string) $result;
    }

    /**
     * @param LabelInterface $label
     * @return array
     * @throws TextRenderException
     */
    private function calculatePricesForLabel(LabelInterface $label): array
    {
        $renderSettings = $label->getExtensionAttributes()->getRenderSettings();

        if ($renderSettings === null || $renderSettings->getProduct() === null) {
            throw new TextRenderException(__('Label wasn\'t initialized...'));
        }

        $labelId = $label->getLabelId();
        $product = $renderSettings->getProduct();
        $productId = $product->getId();

        if (empty($this->cache[$productId][$labelId])) {
            $regularPrice = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
            $specialPrice = $this->getProductSpecialPrice->execute($product);

            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                $regularPrice = $this->priceCurrency->convertAndRound($regularPrice);
            }

            $this->cache[$productId][$labelId] = [
              self::PRICE => $regularPrice,
              self::SPECIAL_PRICE => $specialPrice
            ];
        }

        return $this->cache[$productId][$labelId];
    }

    private function convertPrice($price): string
    {
        $store = $this->storeManager->getStore();

        return $this->priceCurrency->convertAndFormat($price, $store);
    }

    protected function getMinimalPrice(Product $product)
    {
        $minimalPrice = $product->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue();

        if ($product->getTypeId() == Grouped::TYPE_CODE) {
            $associatedProducts = $product->getTypeInstance()->getAssociatedProducts($product);

            foreach ($associatedProducts as $item) {
                $temp = $this->catalogData->getTaxPrice($item, $item->getFinalPrice(), true);

                if ($minimalPrice === null || $temp < $minimalPrice) {
                    $minimalPrice = $temp;
                }
            }
        }

        return $minimalPrice;
    }

    protected function getMaximalPrice(Product $product)
    {
        $maximalPrice = 0;

        if ($product->getTypeId() == Grouped::TYPE_CODE) {
            $associatedProducts = $this->getUsedProducts($product);
            foreach ($associatedProducts as $item) {
                $qty = $item->getQty() * 1 ? $item->getQty() * 1 : 1;
                $maximalPrice += $qty * $item->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
            }
        }

        if (!$maximalPrice) {
            $maximalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        }

        return $maximalPrice;
    }

    private function getUsedProducts(Product $product)
    {
        $result = [];
        $typeInstance = $product->getTypeInstance();

        switch ($product->getTypeId()) {
            case Configurable::TYPE_CODE:
                $result = $this->configurableType->getUsedProducts($product);
                break;
            case Grouped::TYPE_CODE:
                $result = $typeInstance->getAssociatedProducts($product);
                break;
            case BundleType::TYPE_CODE:
                $result = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($product), $product);
                break;
        }

        return $result;
    }

    private function calculateSavePercentage(array $priceConfig): float
    {
        $roundingFunction = $this->configProvider->getRoundingFunctionName();
        $result = 0.;

        if ($priceConfig[self::PRICE] != 0.) {
            $priceDifference = $priceConfig[self::PRICE] - $priceConfig[self::SPECIAL_PRICE];
            $result = $roundingFunction($priceDifference * 100 / $priceConfig[self::PRICE]);
        }

        return $result;
    }
}
