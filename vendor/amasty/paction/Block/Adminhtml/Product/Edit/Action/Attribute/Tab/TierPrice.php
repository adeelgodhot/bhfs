<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Block\Adminhtml\Product\Edit\Action\Attribute\Tab;

use Amasty\Base\Model\MagentoVersion;
use Amasty\Paction\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\TierPrice\Checkbox;
use Amasty\Paction\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\TierPrice\Content;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Block\Adminhtml\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

class TierPrice extends Form implements TabInterface
{
    const TIER_PRICE_CHANGE_CHECKBOX_NAME = 'tier_price_checkbox';
    const TIER_PRICE_CHECKBOX_ID = 'id';

    /**
     * @var MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        MagentoVersion $magentoVersion,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->magentoVersion = $magentoVersion;
    }

    /**
     * Tab settings
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return $this->getTitleDependFromVersion();
    }

    /**
     * @return Phrase
     */
    public function getTabTitle()
    {
        return $this->getTitleDependFromVersion();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Phrase
     */
    private function getLegend()
    {
        return $this->getTitleDependFromVersion();
    }

    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setFieldNameSuffix('attributes');
        $fieldset = $form->addFieldset('tiered_price', ['legend' => $this->getLegend()]);

        $fieldset->addField(
            'tier_price',
            'text',
            [
                'name' => 'tier_price',
                'class' => 'requried-entry',
                'label' => $this->getTabLabel(),
                'title' => $this->getTabTitle()
            ]
        );

        $form->getElement(
            'tier_price'
        )->setRenderer(
            $this->getLayout()->createBlock(Content::class)
        );
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param string $html
     * @return string
     */
    protected function _afterToHtml($html)
    {
        $tierPriceCheckboxHtml = $this->getLayout()->createBlock(
            Checkbox::class,
            '',
            [
                'data' => [
                    self::TIER_PRICE_CHECKBOX_ID => $this->getId()
                ]
            ]
        )->toHtml();

        return parent::_afterToHtml($html) . $tierPriceCheckboxHtml;
    }

    private function getTitleDependFromVersion(): Phrase
    {
        return version_compare($this->magentoVersion->get(), '2.2', '>=')
            ? __('Advanced Pricing')
            : __('Tier Prices');
    }
}
