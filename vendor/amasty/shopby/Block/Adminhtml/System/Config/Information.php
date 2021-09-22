<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Block\Adminhtml\System\Config;

use Amasty\Shopby\Helper\FilterSetting;
use Amasty\Shopby\Model\Source\DisplayMode;
use Amasty\ShopbyBase\Api\Data\FilterSettingInterface;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionExtended;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Model\ScopeInterface;

class Information extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var string
     */
    private $userGuide = 'https://amasty.com/docs/doku.php?id=magento_2:improved_layered_navigation';

    /**
     * @var array
     */
    private $enemyExtensions = [];

    /**
     * @var string
     */
    private $content;

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $this->setContent(__('Please update Amasty Base module. Re-upload it and replace all the files.'));

        $this->_eventManager->dispatch(
            'amasty_base_add_information_content',
            ['block' => $this]
        );

        $html .= $this->getContent();
        $html .= $this->_getFooterHtml($element);

        $html = str_replace(
            'amasty_information]" type="hidden" value="0"',
            'amasty_information]" type="hidden" value="1"',
            $html
        );
        $html = preg_replace('(onclick=\"Fieldset.toggleCollapse.*?\")', '', $html);

        return $html;
    }

    /**
     * @return array
     */
    public function getAdditionalModuleContent()
    {
        $names = $this->getFilterNames();
        if ($names) {
            $result[] = [
                'type' => 'message-notice',
                'text' => __(
                    'Dropdown Display Mode for filters/attributes used in navigation is deprecated and will be '
                    . 'removed soon from the extension. Some filter(s) currently use(s) this mode: '
                    . '%1. Please make sure to change the mode for these filters/attributes to the appropriate '
                    . 'one, otherwise it will be changed to Labels Display Mode mode automatically upon one of the '
                    . 'future updates.',
                    implode(', ', $names)
                )
            ];
        }

        return $result ?? [];
    }

    private function getFilterNames(): array
    {
        return array_merge($this->getAttributeNames(), $this->getCustomFilterNames());
    }

    private function getAttributeNames(): array
    {
        $collection = $this->getData('filterCollectionFactory')->create();
        $collection->addFieldToFilter(FilterSettingInterface::DISPLAY_MODE, DisplayMode::MODE_DROPDOWN);
        $attributeCodes = [];
        foreach ($collection as $filter) {
            $attributeCodes[] = str_replace(FilterSetting::ATTR_PREFIX, '', $filter->getFilterCode());
        }

        $attributeNames = [];
        if ($attributeCodes) {
            $searchCriteria = $this->getData('searchCriteriaBuilder')
                ->addFilter('attribute_code', $attributeCodes, 'in')
                ->create();
            $attributes = $this->getData('attributeRepository')->getList($searchCriteria);
            foreach ($attributes->getItems() as $attribute) {
                $attributeNames[] = $attribute->getFrontendLabel();
            }
        }

        return $attributeNames;
    }

    private function getCustomFilterNames(): array
    {
        $names = [];
        $configValues = $this->_scopeConfig->getValue(
            'amshopby',
            ScopeInterface::SCOPE_STORE
        );
        foreach (($configValues ?: []) as $key => $fieldset) {
            if (strpos($key, 'filter') === false
                || !isset($fieldset['display_mode'])
                || !isset($fieldset['label'])
            ) {
                continue;
            }

            if ($fieldset['display_mode'] === '1') {
                $names[] = $fieldset['label'];
            }
        }

        return $names;
    }

    /**
     * @return string
     */
    public function getUserGuide()
    {
        return $this->userGuide;
    }

    /**
     * @param string $userGuide
     */
    public function setUserGuide($userGuide)
    {
        $this->userGuide = $userGuide;
    }

    /**
     * @return array
     */
    public function getEnemyExtensions()
    {
        return $this->enemyExtensions;
    }

    /**
     * @param array $enemyExtensions
     */
    public function setEnemyExtensions($enemyExtensions)
    {
        $this->enemyExtensions = $enemyExtensions;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
}
