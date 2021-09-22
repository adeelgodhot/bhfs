<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Model\Layer\Filter;

use Amasty\ShopbyBase\Model\OptionSettingRepository;
use Magento\Catalog\Model\Layer;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Exception\StateException;
use Amasty\Shopby\Helper\FilterSetting;
use Magento\Search\Api\SearchInterface;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Amasty\Shopby\Model\Layer\Filter\Traits\FilterTrait;
use Magento\Framework\Filter\StripTags as TagFilter;
use Magento\Catalog\Model\Layer\Filter\ItemFactory as FilterItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Amasty\Shopby\Model\Search\RequestGenerator as ShopbyRequestGenerator;
use \Magento\Store\Model\Store;

class Attribute extends AbstractFilter
{
    use FilterTrait;

    /**
     * @var TagFilter
     */
    private $tagFilter;

    /**
     * @var FilterSettingInterface
     */
    private $filterSetting;

    /**
     * @var SearchInterface
     */
    private $search;

    /**
     * @var  FilterSetting
     */
    private $settingHelper;

    /**
     * @var  ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Shopby\Model\Request
     */
    private $shopbyRequest;

    /**
     * @var OptionSettingRepository
     */
    private $optionSettingRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    public function __construct(
        FilterItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        ItemDataBuilder $itemDataBuilder,
        TagFilter $tagFilter,
        SearchInterface $search,
        FilterSetting $settingHelper,
        ScopeConfigInterface $scopeConfig,
        \Amasty\Shopby\Model\Request $shopbyRequest,
        OptionSettingRepository $optionSettingRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );

        $this->tagFilter = $tagFilter;
        $this->settingHelper = $settingHelper;
        $this->shopbyRequest = $shopbyRequest;
        $this->scopeConfig = $scopeConfig;
        $this->search = $search;
        $this->optionSettingRepository = $optionSettingRepository;
        $this->messageManager = $messageManager;
    }

    /**
     * Apply attribute option filter to product collection.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        if ($this->isApplied()) {
            return $this;
        }

        $requestedOptionsString = $this->shopbyRequest->getFilterParam($this);

        if (empty($requestedOptionsString)) {
            return $this;
        }

        $requestedOptions = explode(',', $requestedOptionsString);

        $this->setCurrentValue($requestedOptions);
        $this->addState($requestedOptions);

        if (!$this->isMultiSelectAllowed() && count($requestedOptions) > 1) {
            $requestedOptions = array_slice($requestedOptions, 0, 1);
        }

        $attribute = $this->getAttributeModel();
        $id = $attribute->getAttributeId();

        /** @var \Amasty\Shopby\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        if ($this->getFilterSetting()->isUseAndLogic()) {
            foreach ($requestedOptions as $key => $value) {
                $fakeAttributeCode = $this->getFakeAttributeCodeForApply($attribute->getAttributeCode(), $key);
                $productCollection->addFieldToFilter($fakeAttributeCode, $value);
            }
        } else {
            $productCollection->addFieldToFilter($attribute->getAttributeCode(), $requestedOptions);
        }

        return $this;
    }

    /**
     * @param array $values
     */
    private function addState(array $values)
    {
        if (!$this->shouldAddState()) {
            return;
        }

        foreach ($values as $value) {
            $item = $this->_createItem($this->getOptionText($value), $value);
            $this->getLayer()->getState()
                ->addFilter(
                    $item
                );
        }
    }

    /**
     * @return bool
     */
    public function shouldAddState()
    {
        // Could be overwritten in plugins.
        return true;
    }

    /**
     * @return bool
     */
    private function isMultiSelectAllowed()
    {
        return $this->getFilterSetting()->isMultiselect();
    }

    /**
     * @return FilterSettingInterface
     */
    protected function getFilterSetting()
    {
        if ($this->filterSetting === null) {
            $this->filterSetting = $this->settingHelper->getSettingByLayerFilter($this);
        }
        return $this->filterSetting;
    }

    /**
     * @param string $attributeCode
     * @param $key
     * @return string
     */
    private function getFakeAttributeCodeForApply($attributeCode, $key)
    {
        if ($key > 0) {
            $attributeCode .= ShopbyRequestGenerator::FAKE_SUFFIX . $key;
        }

        return $attributeCode;
    }

    /**
     * @return int
     */
    public function getItemsCount()
    {
        return count($this->getItems());
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    public function sortOption($a, $b)
    {
        $pattern = '@^(\d+)@';
        if (preg_match($pattern, $a['label'], $ma) && preg_match($pattern, $b['label'], $mb)) {
            $r = $ma[1] - $mb[1];
            if ($r != 0) {
                return $r;
            }
        }

        return strcasecmp($a['label'], $b['label']);
    }

    /**
     * Get data array for building attribute filter items.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getItemsData()
    {
        $selected = !!$this->shopbyRequest->getFilterParam($this);
        if ($selected && !$this->isVisibleWhenSelected()) {
            return [];
        }

        $options = $this->getOptions();
        $optionsFacetedData = $this->getOptionsFacetedData();

        if (!$optionsFacetedData) {
            return [];
        }

        $this->addItemsToDataBuilder($options, $optionsFacetedData);

        $itemsData = $this->getItemsFromDataBuilder();

        $this->sortItemsByFeatured($itemsData);
        return $itemsData;
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        $attribute = $this->getAttributeModel();
        $options = $attribute->getFrontend()->getSelectOptions();

        if ($this->getFilterSetting()->getSortOptionsBy() == \Amasty\Shopby\Model\Source\SortOptionsBy::NAME) {
            usort($options, [$this, 'sortOption']);
        }

        return $options;
    }

    /**
     * Additional Sort options by is_featured setting
     */
    private function sortItemsByFeatured(array &$options): array
    {
        $attribute = $this->getAttributeModel();
        $filterCode = \Amasty\Shopby\Helper\FilterSetting::ATTR_PREFIX . $attribute->getAttributeCode();
        $featuredOptionArray = [];
        $nonFeaturedOptionArray = [];
        $featuredOptions = $this->optionSettingRepository->getAllFeaturedOptionsArray($this->getStoreId());
        foreach ($options as $option) {
            if ($this->isOptionFeatured($featuredOptions, $filterCode, $option)) {
                $featuredOptionArray[] = $option;
            } else {
                $nonFeaturedOptionArray[] = $option;
            }
        }
        $options = array_merge($featuredOptionArray, $nonFeaturedOptionArray);
        if (count($featuredOptionArray)
            && count($nonFeaturedOptionArray)
            && !$this->filterSetting->getNumberUnfoldedOptions()
        ) {
            $this->filterSetting->setNumberUnfoldedOptions(count($featuredOptionArray));
        }

        return $options;
    }

    /**
     * @param array $options
     * @param string $filterCode
     * @param array $option
     * @return bool
     */
    private function isOptionFeatured($options, $filterCode, $option)
    {
        return isset($options[$filterCode][$option['value']][$this->getStoreId()])
            || isset($options[$filterCode][$option['value']][Store::DEFAULT_STORE_ID]);
    }

    /**
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getOptionsFacetedData()
    {
        $optionsFacetedData = $this->generateOptionsFacetedData();

        if (count($optionsFacetedData)) {
            $optionsFacetedData = $this->convertOptionsFacetedData($optionsFacetedData);
        }

        return $optionsFacetedData;
    }

    /**
     * @param array $optionsFacetedData
     *
     * @return array
     */
    protected function convertOptionsFacetedData($optionsFacetedData)
    {
        $attributeValue = $this->shopbyRequest->getFilterParam($this);
        if ($attributeValue) {
            $values = explode(',', $attributeValue);
            foreach ($values as $value) {
                if (!empty($value) && !array_key_exists($value, $optionsFacetedData)) {
                    $optionsFacetedData[$value] = ['value' => $value, 'count' => 0];
                }
            }
        }

        return $optionsFacetedData;
    }

    /**
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function generateOptionsFacetedData()
    {
        /** @var \Amasty\Shopby\Model\ResourceModel\Fulltext\Collection $productCollectionOrigin */
        $productCollectionOrigin = $this->getLayer()->getProductCollection();
        $attribute = $this->getAttributeModel();

        try {
            $optionsFacetedData = $productCollectionOrigin->getFacetedData(
                $attribute->getAttributeCode(),
                $this->getSearchResult()
            );
        } catch (StateException $e) {
            if (!$this->messageManager->hasMessages()) {
                $this->messageManager->addErrorMessage(
                    __(
                        'Make sure that "%1" attribute can be used in layered navigation',
                        $attribute->getAttributeCode()
                    )
                );
            }
            $optionsFacetedData = [];
        }

        return $optionsFacetedData;
    }

    private function getSearchResult(): ?SearchResultInterface
    {
        $searchResult = null;

        if ($this->hasCurrentValue() && !$this->getFilterSetting()->isUseAndLogic()) {
            $searchCriteria = $this->getProductCollection()->getSearchCriteria([$this->getAttributeCode()]);
            $searchResult = $this->search->search($searchCriteria);
        }

        return $searchResult;
    }

    /**
     * @param array $options
     * @param array $optionsFacetedData
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addItemsToDataBuilder($options, $optionsFacetedData)
    {
        if (!$options) {
            return;
        }
        foreach ($options as $option) {
            if (empty($option['value'])) {
                continue;
            }

            $isFilterableAttribute = $this->getAttributeIsFilterable($this->getAttributeModel());
            if (isset($optionsFacetedData[$option['value']])
                || $isFilterableAttribute != static::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS
            ) {
                $count = 0;
                if (isset($optionsFacetedData[$option['value']]['count'])) {
                    $count = $optionsFacetedData[$option['value']]['count'];
                }
                $this->itemDataBuilder->addItemData(
                    $this->tagFilter->filter($option['label']),
                    $option['value'],
                    $count
                );
            }
        }
    }

    /**
     * Get items data according to attribute settings.
     * @return array
     */
    private function getItemsFromDataBuilder()
    {
        $itemsData = $this->itemDataBuilder->build();
        if (count($itemsData) == 1
            && !$this->isOptionReducesResults(
                $itemsData[0]['count'],
                $this->getLayer()->getProductCollection()->getSize()
            )
        ) {
            $itemsData = $this->getReducedItemsData($itemsData);
        }

        return $itemsData;
    }
}
