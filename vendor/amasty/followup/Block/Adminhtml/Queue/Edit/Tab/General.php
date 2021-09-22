<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Block\Adminhtml\Queue\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Amasty\Followup\Controller\RegistryConstants;

class General extends Generic implements TabInterface{

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


    protected function _getQueue()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_AMFOLLOWUP_QUEUE);
    }


    protected function _prepareForm()
    {

        $model = $this->_getQueue();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('amasty_queue_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General')]);

        if ($model->getId()) {
            $fieldset->addField('history_id', 'hidden', ['name' => 'history_id']);
        }

        $fieldset->addField(
            'email',
            'text',
            ['name' => 'email', 'label' => __('Email'), 'title' => __('Email'), 'required' => true]
        );

        $fieldset->addField(
            'subject',
            'text',
            ['name' => 'subject', 'label' => __('Subject'), 'title' => __('Subject'), 'required' => true]
        );

        $fieldset->addField(
            'body',
            'textarea',
            [
                'name' => 'body',
                'label' => __('Body'),
                'title' => __('Body'),
                'required' => true,
                'style' => 'height:24em',
            ]
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }


}