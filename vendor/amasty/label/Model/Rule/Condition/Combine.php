<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Rule\Condition;

use Magento\CatalogRule\Model\Rule\Condition\Combine as MagentoCombineRule;
use Magento\Framework\Phrase;
use Magento\Rule\Model\Condition\AbstractCondition;

class Combine extends MagentoCombineRule
{
    const CUSTOM_CONDITIONS = 'amlabel_custom_condition';
    const SORTING_CONDITIONS = 'amasty_sorting';

    public function getNewChildSelectOptions()
    {
        $conditions = AbstractCondition::getNewChildSelectOptions();
        $conditions = array_merge($conditions, [
            [
                'label' => __('Conditions Combination'),
                'value' => static::class
            ],
            [
                'label' => __('Custom conditions'),
                'value' => $this->getCustomConditions()
            ],
            [
                'label' => __('Product Attribute'),
                'value' => $this->getProductConditions()
            ],
            [
                'label' => $this->getSortingConditionsLabel(),
                'value' => $this->isSortingEnabled() ? $this->getSortingConditions() : []
            ]
        ]);

        return $conditions;
    }

    /**
     * @return array[]
     */
    private function getCustomConditions(): array
    {
        $customConditions = (array) $this->getData(self::CUSTOM_CONDITIONS);

        return array_reduce($customConditions, function (array $carry, $condition): array {
            if ($condition instanceof AbstractCondition) {
                $carry[] = [
                    'label' => $condition->getAttributeElementHtml(),
                    'value' => ltrim(get_class($condition), '/')
                ];
            }

            return $carry;
        }, []);
    }

    /**
     * @return array[]
     */
    private function getSortingConditions(): array
    {
        $sortingConditions = (array) $this->getData(self::SORTING_CONDITIONS);

        return array_reduce($sortingConditions, function (array $carry, $condition): array {
            if ($condition instanceof AbstractCondition) {
                $carry[] = [
                    'label' => $condition->getAttributeElementHtml(),
                    'value' => ltrim(get_class($condition), '/')
                ];
            }

            return $carry;
        }, []);
    }

    private function getSortingConditionsLabel(): Phrase
    {
        $title = __('Improved Sorting (not installed)');
        if ($this->isSortingEnabled()) {
            $title = __('Improved Sorting');
        }
        
        return $title;
    }
    
    private function isSortingEnabled(): bool
    {
        return $this->getData('module_manager') && $this->getData('module_manager')->isEnabled('Amasty_Sorting');
    }

    private function getProductConditions(): array
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];

        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Magento\CatalogRule\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }

        return $attributes;
    }

    public function getOperatorElementHtml()
    {
        return $this->getOperatorElement()->getHtml();
    }
}
