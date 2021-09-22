<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Block\Adminhtml\Rule\Edit\Tab\Schedule;

use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Amasty\Followup\Controller\RegistryConstants;

class Content extends Widget implements RendererInterface
{
    const MAX_SALES_RULES = 100;

    protected $_template = 'rule/schedule.phtml';
    protected $_salesRuleCollection;
    protected $_emailTemplateCollection;
    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\SalesRule\Model\Rule $rule,
        \Amasty\Followup\Helper\Data $helper,
        array $data = []
    ) {

        $this->_coreRegistry = $registry;
        $this->_helper = $helper;

        $this->_emailTemplateCollection = $this->_helper->getEmailTemplatesCollection(
            $this->_getRule()->getStartEventType()
        );

        $this->_salesRuleCollection = $rule->getCollection()
            ->addFilter('use_auto_generation', 1);


        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add Record'), 'onclick' => 'return amastyFollowupSchedule.addItem();', 'class' => 'add']
        );

        $button->setName('add_record_button');

        $this->setChild('add_record_button', $button);


        return parent::_prepareLayout();
    }

    public function getAddRecordButtonHtml()
    {
        return $this->getChildHtml('add_record_button');
    }


    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * @param $number
     * @param int|bool $default
     * @return string
     */
    public function getNumberOptions($number, $default = false)
    {
        $ret = array('<option value="">-</option>');
        for($index = 1; $index <= $number; $index++){
            $ret[] = '<option value="' . $index . '"'
                . (($default && ($index == $default)) ? ' selected="selected" ' : '') .  ' >' . $index . '</option>';
        }
        return implode('', $ret);
    }

    public function getEmailTemplateCollection()
    {
        return $this->_emailTemplateCollection;
    }

    public function getSalesRuleCollection()
    {
        return $this->_salesRuleCollection;
    }

    public function isShowSalesRuleSelect()
    {
        return $this->getSalesRuleCollection()->getSize() < self::MAX_SALES_RULES;
    }

    protected function _getRule()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_AMFOLLOWUP_RULE);
    }

    public function getScheduleCollection()
    {
        return $this->_getRule()->getScheduleCollection();
    }

}