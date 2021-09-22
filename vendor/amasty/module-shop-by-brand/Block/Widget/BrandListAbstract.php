<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Block\Widget;

use Amasty\ShopbyBase\Api\UrlBuilderInterface;
use Amasty\ShopbyBase\Model\OptionSettingFactory;
use Amasty\ShopbyBrand\Helper\Data;
use Amasty\ShopbyBrand\Model\BrandSettingProvider;
use Amasty\ShopbyBrand\Model\ProductCount;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\View\Element\Template\Context;
use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Eav\Model\Entity\Attribute\Option;
use Amasty\ShopbyBrand\Helper\Data as DataHelper;

abstract class BrandListAbstract extends \Magento\Framework\View\Element\Template
{
    const PATH_BRAND_ATTRIBUTE_CODE = 'amshopby_brand/general/attribute_code';

    /**
     * @var  array|null
     */
    protected $items;

    /**
     * @var ProductCount
     */
    protected $productCount;

    /**
     * @var DataHelper
     */
    protected $helper;

    /**
     * @var OptionSettingFactory
     */
    private $optionSettingFactory;

    /**
     * @var UrlBuilderInterface
     */
    private $amUrlBuilder;

    /**
     * @var BrandSettingProvider
     */
    private $brandSettingProvider;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        OptionSettingFactory $optionSettingFactory,
        DataHelper $helper,
        UrlBuilderInterface $amUrlBuilder,
        ProductCount $productCount,
        BrandSettingProvider $brandSettingProvider,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->optionSettingFactory = $optionSettingFactory;
        $this->amUrlBuilder = $amUrlBuilder;
        $this->productCount = $productCount;
        $this->brandSettingProvider = $brandSettingProvider;
        $this->dataPersistor = $dataPersistor;

        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getItems()
    {
        if ($this->items === null) {
            $this->items = [];
            $attributeCode = $this->helper->getBrandAttributeCode();

            if (!$attributeCode) {
                return $this->items;
            }

            $options = $this->helper->getBrandOptions();
            array_shift($options);
            $storeId = (int)$this->_storeManager->getStore()->getId();

            foreach ($options as $option) {
                $optionValue = (int)$option->getValue();
                $setting = $this->brandSettingProvider->getItemByStoreIdAndValue($storeId, $optionValue)
                    ?? $this->optionSettingFactory->create();
                $data = $this->getItemData($option, $setting);

                if ($data) {
                    $this->items[] = $data;
                }
            }
        }

        return $this->items;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Option $option
     * @param \Amasty\ShopbyBase\Api\Data\OptionSettingInterface $setting
     * @return array
     */
    abstract protected function getItemData(Option $option, OptionSettingInterface $setting);

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Option $option
     * @return string
     */
    public function getBrandUrl(Option $option)
    {
        return $this->amUrlBuilder->getUrl('ambrand/index/index', ['id' => $option->getValue()]);
    }

    /**
     * @return DataPersistorInterface
     */
    public function getDataPersistor(): DataPersistorInterface
    {
        return $this->dataPersistor;
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function _sortByName($a, $b)
    {
        $a['label'] = trim($a['label']);
        $b['label'] = trim($b['label']);

        if ($a == '') {
            return 1;
        }
        if ($b == '') {
            return -1;
        }

        $x = substr($a['label'], 0, 1);
        $y = substr($b['label'], 0, 1);
        if (is_numeric($x) && !is_numeric($y)) {
            return 1;
        }
        if (!is_numeric($x) && is_numeric($y)) {
            return -1;
        }

        if (function_exists('mb_strtoupper')) {
            $res = strcmp(mb_strtoupper($a['label']), mb_strtoupper($b['label']));
        } else {
            $res = strcmp(strtoupper($a['label']), strtoupper($b['label']));
        }
        return $res;
    }

    protected function _beforeToHtml()
    {
        $this->initializeBlockConfiguration();
        $this->applySorting();

        return parent::_beforeToHtml();
    }

    /**
     * deprecated. used for back compatibility.
     */
    public function initializeBlockConfiguration(): void
    {
        $configValues = $this->_scopeConfig->getValue(
            $this->getConfigValuesPath(),
            ScopeInterface::SCOPE_STORE
        );
        foreach (($configValues ?: []) as $option => $value) {
            if ($this->getData($option) === null) {
                $this->setData($option, $value);
            }
        }
    }

    /**
     * Apply additional sorting before render html
     *
     * @return $this
     */
    protected function applySorting()
    {
        return $this;
    }

    abstract protected function getConfigValuesPath(): string;

    /**
     * Get brand product count
     *
     * @param int $optionId
     * @return int
     */
    protected function _getOptionProductCount($optionId)
    {
        return $this->productCount->get($optionId);
    }

    public function isDisplayZero(): bool
    {
        return (bool) $this->getData('display_zero');
    }
}
