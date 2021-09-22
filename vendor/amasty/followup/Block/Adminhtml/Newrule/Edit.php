<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Block\Adminhtml\Newrule;

use Amasty\Followup\Controller\RegistryConstants;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    protected $_coreRegistry = null;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {

        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);


    }

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Amasty_Followup';
        $this->_controller = 'adminhtml_newrule';

        parent::_construct();

        $this->buttonList->add(
            'continue',
            [
                'class' => 'save primary',
                'label' => __('Continue'),
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form']],
                ]
            ],
            10
        );

        $this->removeButton('save');

    }
}