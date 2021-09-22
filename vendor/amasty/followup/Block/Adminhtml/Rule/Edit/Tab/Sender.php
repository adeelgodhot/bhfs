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

class Sender extends Generic implements TabInterface{

    protected $_systemStore;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Sender Details');
    }

    public function getTabTitle()
    {
        return __('Sender Details');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _getRule()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_AMFOLLOWUP_RULE);
    }

    protected function _prepareForm()
    {
        $model = $this->_getRule();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('amasty_rule_');

        $fldInfo = $form->addFieldset('analytics_fieldset', ['legend' => __('Sender Details')]);

        $fldInfo->addField('sender_name', 'text', array(
            'label'     => __('Name'),
            'name'      => 'sender_name'
        ));

        $fldInfo->addField('sender_email', 'text', array(
            'label'     => __('Email'),
            'name'      => 'sender_email'
        ));

        $fldInfo->addField('sender_cc', 'text', array(
            'label'     => __('Sends copy of emails to'),
            'name'      => 'sender_cc'
        ));

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }
}