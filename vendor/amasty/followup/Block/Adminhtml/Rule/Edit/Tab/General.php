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
use Amasty\Followup\Helper\Data;
use Magento\Config\Model\Config\Source\Yesno;

class General extends Generic implements TabInterface{

    protected $_activeOptions;
    protected $_yesNo;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        Data $amprhelper,
        \Amasty\Followup\Ui\Component\Listing\Column\Rule\Active\Options $activeOptions,
        Yesno $yesNo,
        array $data = []
    ) {
        $this->_activeOptions = $activeOptions;
        $this->amprhelper = $amprhelper;
        $this->_yesNo = $yesNo;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('General');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */

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

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General')]);

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            ['name' => 'name', 'label' => __('Name'), 'title' => __('Name'), 'required' => true]
        );

        $fieldset->addField(
            'start_event_type',
            'select',
            [
                'label' => __('Start Event'),
                'title' => __('Start Event'),
                'name' => 'start_event_type',
                'readonly' => !$model->getId() ? false : true,
                'disabled' => !$model->getId() ? false : true,
                'required' => true,
                'values' => $this->amprhelper->getEventTypes()
            ]
        );

        if ($this->_getRule()->getStartEventType() == \Amasty\Followup\Model\Rule::TYPE_CUSTOMER_DATE)
        {
            $dateFormat = $this->_localeDate->getDateFormat(
                \IntlDateFormatter::SHORT
            );
            $fieldset->addField('customer_date_event', 'date', array(
                'label'        => __('Date'),
                'required'     => true,
                'name'         => 'customer_date_event',
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'format'       => $dateFormat
            ));
        }

        $cancelTypes = array();
        foreach($this->amprhelper->getCancelTypes($model->isOrderRelated()) as $key => $val){
            $cancelTypes[] = array(
                "value" => $key,
                "label" => $val
            );
        }

        $fieldset->addField('cancel_event_type', 'multiselect', array(
            'label'     => __('Cancel Event'),
            'name'      => 'cancel_event_type[]',
            'values'    => $cancelTypes,
        ));

        if ($this->_getRule()->getStartEventType() != \Amasty\Followup\Model\Rule::TYPE_CUSTOMER_SUBSCRIPTION) {
            $fieldset->addField('to_subscribers', 'select', array(
                'label'     => __('Send to Newsletter Subscribers Only'),
                'name'      => 'to_subscribers',
                'values'    => $this->_yesNo->toOptionArray()
            ));
        }

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'options' => $this->_activeOptions->toArray()
            ]
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }


}