<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrand
 */


declare(strict_types=1);

namespace Amasty\ShopbyBrand\Block\Widget;

use Amasty\ShopbyBase\Api\Data\OptionSettingInterface;
use Amasty\ShopbyBrand\Model\Source\SliderSort;
use \Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Widget\Block\BlockInterface;

class BrandSlider extends BrandListAbstract implements BlockInterface
{
    const HTML_ID = 'amslider_id';

    const DEFAULT_IMG_WIDTH = 130;

    /**
     * deprecated. used for back compatibility.
     */
    const CONFIG_VALUES_PATH = 'amshopby_brand/slider';

    protected function getItemData(Option $option, OptionSettingInterface $setting): array
    {
        $result = [];
        if ($setting->getIsShowInSlider()
            && ($this->isDisplayZero() || $this->_getOptionProductCount($setting->getValue()))
        ) {
            $result = [
                'brandId' => $option->getValue(),
                'label' => $setting->getLabel() ?: $option->getLabel(),
                'url' => $this->helper->getBrandUrl($option),
                'img' => $setting->getSliderImageUrl(),
                'position' => $setting->getSliderPosition(),
                'alt' => $setting->getSmallImageAlt() ?: $setting->getLabel()
            ];
        }

        return $result;
    }

    protected function applySorting(): self
    {
        if ($this->getData('sort_by') == SliderSort::NAME) {
            usort($this->items, [$this, '_sortByName']);
        } else {
            usort($this->items, [$this, '_sortByPosition']);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getSliderOptions()
    {
        $options = [];
        $itemsPerView = max(1, $this->getItemNumber());
        $options['slidesPerView'] = $itemsPerView;
        $options['loop'] = $this->getData('infinity_loop') ? 'true' : 'false';
        $options['simulateTouch'] = $this->getData('simulate_touch') ? 'true' : 'false';
        if ($this->getData('pagination_show')) {
            $options['pagination'] = '".swiper-pagination"';
            $options['paginationClickable'] = 'true';
        }

        if ($this->getData('autoplay')) {
            $options['autoplay'] = (int)$this->getData('autoplay_delay');
        }

        return $options;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!count($this->getItems())) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function _sortByPosition($a, $b)
    {
        return $a['position'] - $b['position'];
    }

    public function getHeaderColor(): string
    {
        return (string) $this->getData('slider_header_color');
    }

    public function getTitleColor(): string
    {
        return (string) $this->getData('slider_title_color');
    }

    public function getTitle(): string
    {
        return (string) $this->getData('slider_title');
    }

    public function getItemNumber(): int
    {
        return (int) $this->getData('items_number');
    }

    public function isSliderEnabled(): bool
    {
        return count($this->getItems()) > $this->getItemNumber();
    }

    protected function getConfigValuesPath(): string
    {
        return self::CONFIG_VALUES_PATH;
    }
}
