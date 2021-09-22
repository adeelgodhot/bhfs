<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


namespace Amasty\ShopbyBrand\Block\Widget;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBrand\Model\Source\Tooltip;
use Magento\Framework\View\Element\Template as Template;
use \Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Widget\Block\BlockInterface;

class BrandList extends BrandListAbstract implements BlockInterface
{
    /**
     * deprecated. leave for back compatibility.
     */
    const CONFIG_VALUES_PATH = 'amshopby_brand/brands_landing';

    /**
     * @return array
     */
    public function getIndex()
    {
        $items = $this->getItems();
        if (!$items) {
            return [];
        }

        $letters = $this->sortByLetters($items);
        $index = $this->breakByColumns($letters);

        return $index;
    }

    /**
     * @param array $items
     *
     * @return array
     */
    private function sortByLetters($items)
    {
        $this->sortItems($items);
        $letters = $this->items2letters($items);

        return $letters;
    }

    /**
     * @param array $letters
     *
     * @return array
     */
    private function breakByColumns($letters)
    {
        $columnCount = abs((int)$this->getData('columns'));
        if (!$columnCount) {
            $columnCount = 1;
        }

        $row = 0; // current row
        $num = 0; // current number of items in row
        $index = [];
        foreach ($letters as $letter => $items) {
            $index[$row][$letter] = $items['items'];
            $num++;
            if ($num >= $columnCount) {
                $num = 0;
                $row++;
            }
        }

        return $index;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Option $option
     * @param OptionSettingInterface $setting
     * @return array
     */
    protected function getItemData(Option $option, OptionSettingInterface $setting)
    {
        $count = $this->_getOptionProductCount($setting->getValue());
        if ($this->isDisplayZero() || $count) {
            $result = [
                'brandId' => $option->getData('value'),
                'label' => $setting->getLabel() ?: $option->getLabel(),
                'url' => $this->helper->getBrandUrl($option),
                'img' => $setting->getSliderImageUrl(),
                'image' => $setting->getImageUrl(),
                'description' => $setting->getDescription(true),
                'short_description' => $setting->getShortDescription(),
                'cnt' => $count,
                'alt' => $setting->getSmallImageAlt() ?: $setting->getLabel()
            ];
        }

        return $result ?? [];
    }

    /**
     * Get brand product count
     *
     * @param int $optionId
     * @return int
     */
    protected function _getOptionProductCount($optionId)
    {
        if ($this->isShowCount() || !$this->isDisplayZero()) {
            return parent::_getOptionProductCount($optionId);
        }

        return 0;
    }

    /**
     * @param array $items
     */
    protected function sortItems(array &$items)
    {
        usort($items, [$this, '_sortByName']);
    }

    /**
     * @param array $items
     * @return array
     */
    protected function items2letters($items)
    {
        $letters = [];
        foreach ($items as $item) {
            $letter = $this->getLetter($item['label']);
            if (!isset($letters[$letter]['items'])) {
                $letters[$letter]['items'] = [];
            }

            $letters[$letter]['items'][] = $item;
            if (!isset($letters[$letter]['count'])) {
                $letters[$letter]['count'] = 0;
            }

            $letters[$letter]['count']++;
        }

        return $letters;
    }

    /**
     * @param $item
     * @return false|mixed|string|string[]|null
     */
    public function getLetter($label)
    {
        if (function_exists('mb_strtoupper')) {
            $letter = mb_strtoupper(mb_substr($label, 0, 1, 'UTF-8'));
        } else {
            $letter = strtoupper(substr($label, 0, 1));
        }

        if (is_numeric($letter)) {
            $letter = '#';
        }

        return $letter;
    }

    /**
     * @return array
     */
    public function getAllLetters()
    {
        $brandLetters = [];
        /** @codingStandardsIgnoreStart */
        foreach ($this->getIndex() as $letters) {
            $brandLetters = array_merge($brandLetters, array_keys($letters));
        }
        /** @codingStandardsIgnoreEnd */

        return $brandLetters;
    }

    /**
     * @return string
     */
    public function getSearchHtml()
    {
        $html = '';
        if (!$this->isShowSearch() || !$this->getItems()) {
            return $html;
        }

        $searchCollection = [];
        foreach ($this->getItems() as $item) {
            $searchCollection[$item['url']] = $item['label'];
        }

        /** @var Template $block */
        $block = $this->getSearchBrandBlock();
        if ($block) {
            $searchCollection = json_encode($searchCollection);
            $block->setBrands($searchCollection);
            $html = $block->toHtml();
        }

        return $html;
    }

    /**
     * @return bool|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getSearchBrandBlock()
    {
        $block = $this->getLayout()->getBlock('ambrands.search');
        if (!$block) {
            $block = $this->getLayout()->createBlock(Template::class, 'ambrands.search')
                ->setTemplate('Amasty_ShopbyBrand::brand_search.phtml');
        }

        return $block;
    }

    public function isTooltipEnabled(): bool
    {
        $setting = $this->helper->getModuleConfig('general/tooltip_enabled');

        return in_array(Tooltip::ALL_BRAND_PAGE, explode(',', $setting));
    }

    public function getTooltipAttribute(array $item): string
    {
        if ($this->isTooltipEnabled()) {
            $result = $this->helper->generateToolTipContent($item);
        }

        return $result ?? '';
    }

    public function getImageWidth(): int
    {
        return abs((int) $this->getData('image_width')) ?: 100;
    }

    public function getImageHeight(): int
    {
        return abs((int) $this->getData('image_height')) ?: 50;
    }

    public function isShowBrandLogo(): bool
    {
        return (bool) $this->getData('show_images');
    }

    public function isShowSearch(): bool
    {
        return (bool) $this->getData('show_search');
    }

    public function isShowFilter(): bool
    {
        return (bool) $this->getData('show_filter');
    }

    public function isFilterDisplayAll(): bool
    {
        return (bool) $this->getData('filter_display_all');
    }

    public function isShowCount(): bool
    {
        return (bool) $this->getData('show_count');
    }

    protected function getConfigValuesPath(): string
    {
        return self::CONFIG_VALUES_PATH;
    }
}
