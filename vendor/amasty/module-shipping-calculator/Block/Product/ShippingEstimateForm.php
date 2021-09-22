<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingCalculator
 */


namespace Amasty\ShippingCalculator\Block\Product;

use Magento\Directory\Block\Data;
use Magento\Catalog\Model\Product;

class ShippingEstimateForm extends Data
{
    /**
     * @var \Amasty\ShippingCalculator\Model\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var Product
     */
    private $product;

    /**
     * ShippingEstimateForm constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Amasty\ShippingCalculator\Model\ConfigProvider $configProvider
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Amasty\ShippingCalculator\Model\ConfigProvider $configProvider,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $directoryHelper, $jsonEncoder, $configCacheType, $regionCollectionFactory,
            $countryCollectionFactory, $data);
        $this->configProvider = $configProvider;
        $this->registry = $registry;

        $this->setTitle($this->configProvider->getTabName());
    }

    /**
     * @param string $blockPlace
     * @return bool
     */
    public function canShowBlock($blockPlace)
    {
        if (!$this->configProvider->isEnabled()) {
            return false;
        }

        if (!$this->configProvider->isShowCountyField() && !$this->configProvider->isShowPostcodeField()) {
            return false;
        }

        if (!in_array($blockPlace, $this->configProvider->getPlacesForDisplay())) {
            return false;
        }

        $product = $this->getProduct();
        if (in_array($product->getId(), $this->configProvider->getRestrictedProductsIds())) {
            return false;
        }
        $productCategoriesIds = $product->getCategoryIds();

        foreach ($productCategoriesIds as $productCategoryId) {
            if (in_array($productCategoryId, $this->configProvider->getRestrictedCategoriesIds())) {
                return false;
            }
        }

        // check virtual product
        if ($product->getIsVirtual()) {
            return false;
        }

        return true;
    }

    /**
     * @return \Amasty\ShippingCalculator\Model\ConfigProvider
     */
    public function getConfigProvider()
    {
        return $this->configProvider;
    }

    /**
     * @return Product
     */
    private function getProduct()
    {
        if (!$this->product) {
            $this->product = $this->registry->registry('product');
        }
        return $this->product;
    }
}
