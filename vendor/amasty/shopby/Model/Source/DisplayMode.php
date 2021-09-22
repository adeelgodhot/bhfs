<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Model\Source;

class DisplayMode implements \Magento\Framework\Option\ArrayInterface
{
    const MODE_DEFAULT = 0;
    const MODE_DROPDOWN = 1;
    const MODE_SLIDER = 2;
    const MODE_FROM_TO_ONLY = 3;
    const MODE_IMAGES = 4;
    const MODE_IMAGES_LABELS = 5;
    const MODE_TEXT_SWATCH = 6;
    const SELECT = 'select';
    const DISPLAY_MODE_FRONTEND_INPUT_MAP = [
        'visual' => DisplayMode::MODE_IMAGES,
        'text' => DisplayMode::MODE_TEXT_SWATCH
    ];
    const ATTRUBUTE_DEFAULT = 'default';
    const ATTRUBUTE_PRICE = 'price';

    /**
     * @var string
     */
    protected $attributeType = self::ATTRUBUTE_DEFAULT;

    /**
     * @var  \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $attribute;

    /**
     * @param $attributeType
     * @return $this
     */
    public function setAttributeType($attributeType)
    {
        $this->attributeType = $attributeType;
        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return $this
     */
    public function setAttribute(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
    {
        $this->setAttributeType($attribute->getBackendType());
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * @return bool
     */
    private function showSwatchOptions()
    {
        return $this->attribute
            && $this->attribute->getId()
            && in_array($this->attribute->getFrontendInput(), ['multiselect', 'select']);
    }

    /**
     * @return bool
     */
    public function showDefaultSwatchOptions()
    {
        return $this->showSwatchOptions() && $this->attribute->getFrontendInput() == 'select';
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getOptions() as $optionValue => $optionLabel) {
            $options[] = ['value' => $optionValue, 'label' => $optionLabel];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getOptions();
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        $options = [
            "" => "",
            self::MODE_DEFAULT => __('Labels'),
            self::MODE_DROPDOWN => __('Dropdown (Deprecated)')
        ];

        if ($this->showSwatchOptions()) {
            $options[self::MODE_IMAGES] = __('Images');
            $options[self::MODE_IMAGES_LABELS] = __('Images & Labels');
        }

        if ($this->showDefaultSwatchOptions()) {
            $options[self::MODE_TEXT_SWATCH] = __('Text Swatches');
        }

        if (isset($this->attribute) && $this->attribute->getFrontendInput() === 'price') {
            $options[self::MODE_SLIDER] = __('Slider');
            $options[self::MODE_FROM_TO_ONLY] = __('From-To Only');
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getInputTypeMap()
    {
        $array = [
            self::ATTRUBUTE_DEFAULT => [
                self::MODE_DEFAULT => 'multiselect',
                self::MODE_DROPDOWN => 'select'
            ],
            self::ATTRUBUTE_PRICE => [
                self::MODE_DEFAULT => self::ATTRUBUTE_PRICE,
                self::MODE_DROPDOWN => self::ATTRUBUTE_PRICE,
                self::MODE_SLIDER => self::ATTRUBUTE_PRICE,
                self::MODE_FROM_TO_ONLY => self::ATTRUBUTE_PRICE,
            ]
        ];

        if ($this->showSwatchOptions()) {
            $array[self::ATTRUBUTE_DEFAULT][self::MODE_IMAGES] =
                \Magento\Swatches\Model\Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT;
            $array[self::ATTRUBUTE_DEFAULT][self::MODE_IMAGES_LABELS] =
                \Magento\Swatches\Model\Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT;
        }

        if ($this->showDefaultSwatchOptions()) {
            $array[self::ATTRUBUTE_DEFAULT][self::MODE_TEXT_SWATCH] =
                \Magento\Swatches\Model\Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT;
        }

        return $array;
    }

    /**
     * @return array
     */
    public function getAllOptionsDependencies()
    {
        $options = [
            self::ATTRUBUTE_DEFAULT => [
                self::MODE_DEFAULT => __('Labels'),
                self::MODE_DROPDOWN => __('Dropdown')
            ],
            self::ATTRUBUTE_PRICE => [
                self::MODE_DEFAULT => __('Ranges'),
                self::MODE_DROPDOWN => __('Dropdown'),
                self::MODE_SLIDER => __('Slider'),
                self::MODE_FROM_TO_ONLY => __('From-To Only'),
            ]
        ];
        if ($this->showSwatchOptions()) {
            $options[self::ATTRUBUTE_DEFAULT][self::MODE_IMAGES] = __('Images');
            $options[self::ATTRUBUTE_DEFAULT][self::MODE_IMAGES_LABELS] = __('Images & Labels');
        }

        if ($this->showDefaultSwatchOptions()) {
            $options[self::ATTRUBUTE_DEFAULT][self::MODE_TEXT_SWATCH] = __('Text Swatches');
        }

        return $options;
    }

    /**
     * @return array
     */
    public function getShowProductQuantitiesConfig()
    {
        return [
            self::MODE_DEFAULT,
            self::MODE_DROPDOWN,
            self::MODE_IMAGES_LABELS
        ];
    }

    /**
     * @return array
     */
    public function getNumberUnfoldedOptionsConfig()
    {
        return [
            self::MODE_DEFAULT,
            self::MODE_IMAGES_LABELS,
            self::MODE_IMAGES,
            self::MODE_TEXT_SWATCH
        ];
    }

    /**
     * @return array
     */
    public function getIsMultiselectConfig()
    {
        return [
            self::MODE_DEFAULT,
            self::MODE_DROPDOWN,
            self::MODE_IMAGES_LABELS,
            self::MODE_IMAGES,
            self::MODE_TEXT_SWATCH
        ];
    }

    /**
     * @return array
     */
    public function getNotices()
    {
        return [
            self::MODE_IMAGES => __('Please upload images at the Properties tab.'),
            self::MODE_IMAGES_LABELS => __('Please upload images at the Properties tab.'),
            self::MODE_TEXT_SWATCH => __('Please add text values at the Properties tab.')
        ];
    }

    /**
     * @return array
     */
    public function getEnabledTypes()
    {
        return [
            self::MODE_DROPDOWN => 'select',
            self::MODE_IMAGES => \Magento\Swatches\Model\Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT,
            self::MODE_TEXT_SWATCH => \Magento\Swatches\Model\Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT
        ];
    }

    /**
     * @return array
     */
    public function getChangeLabels()
    {
        return [
            self::ATTRUBUTE_DEFAULT => [self::MODE_DEFAULT => __('Labels')],
            self::ATTRUBUTE_PRICE => [self::MODE_DEFAULT => __('Ranges')]
        ];
    }
}
