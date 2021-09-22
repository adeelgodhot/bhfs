<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Amasty\Followup\Controller\RegistryConstants;
use Magento\Framework\ObjectManagerInterface;

class Segments extends Generic implements TabInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Amasty\Followup\Helper\Data
     */
    protected $helper;

    /**
     * @var \Amasty\Followup\Model\Factories\SegmentFactory
     */
    protected $segmentFactory;

    /**
     * Segments constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Amasty\Followup\Helper\Data $helper
     * @param ObjectManagerInterface $objectManager
     * @param \Amasty\Followup\Model\Factories\SegmentFactory $segmentFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Followup\Helper\Data $helper,
        ObjectManagerInterface $objectManager,
        \Amasty\Followup\Model\Factories\SegmentFactory $segmentFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->helper = $helper;
        $this->objectManager = $objectManager;
        $this->segmentFactory = $segmentFactory;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Segments');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Segments');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        if ($this->helper->isModuleOutputEnabled(
            \Amasty\Followup\Helper\Data::AMASTY_SEGMENT_MODULE_DEPEND_NAMESPACE
        )) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return mixed
     */
    protected function _getRule()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_AMFOLLOWUP_RULE);
    }

    /**
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_getRule();

        $options = [];
        if ($this->helper->isModuleOutputEnabled(
            \Amasty\Followup\Helper\Data::AMASTY_SEGMENT_MODULE_DEPEND_NAMESPACE
        )) {
            $options = $this->segmentFactory->getSegmentCollection()->addActiveFilter()->toOptionArray();
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('amasty_rule_');

        $fldInfo = $form->addFieldset('segments_fieldset', ['legend' => __('Segments')]);

        $fldInfo->addField(
            'segments_ids',
            'multiselect',
            [
                'name'   => 'segments_ids[]',
                'label'  => $this->getTabLabel(),
                'title'  => $this->getTabTitle(),
                'values' => $options
            ]
        );

        $values = $model->getData();

        if (isset($values['segments']) && !empty($values['segments'])) {
            $values['segments_ids'] = explode(',', $values['segments']);
        }

        $form->setValues($values);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
