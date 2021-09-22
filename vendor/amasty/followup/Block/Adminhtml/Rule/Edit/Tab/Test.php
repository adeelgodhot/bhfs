<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Amasty\Followup\Controller\RegistryConstants;
use Amasty\Followup\Helper\Data;

class Test extends \Magento\Framework\View\Element\Text\ListText implements TabInterface
{
    protected $amprhelper;
    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Data $amprhelper,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->amprhelper = $amprhelper;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Test');
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Test');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    protected function _getRule()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_AMASTY_AMFOLLOWUP_RULE);
    }

    protected function _toHtml() {
        if ($this->_getRule()->getId()) {
            $recipientEmail = $this->amprhelper->getScopeValue('amfollowup/test/recipient');

            $recipientValidated = !empty($recipientEmail) && \Zend_Validate::is($recipientEmail, 'EmailAddress');

            if ($recipientValidated) {

                if ($this->_getRule()->isOrderRelated()){
                    return $this->getLayout()->createBlock(
                        'Amasty\Followup\Block\Adminhtml\Rule\Edit\Tab\Test\Order'
                    )->toHtml();
                } else {
                    return $this->getLayout()->createBlock(
                        'Amasty\Followup\Block\Adminhtml\Rule\Edit\Tab\Test\Customer'
                    )->toHtml();
                }
            } else {

                $url = $this->getUrl('adminhtml/system_config/edit/section/amfollowup');
                $link = '<a target="_blank" href="' . $url . '">' . __('test email') . '</a>';

                $label = __('Before sending test messages, please fill in the %1 in the extension configuration section',
                    $link
                );

                $content = '<div class="message message-warning warning"><div>' . $label . '</div></div>';
                return $content;
            }
        }
        return parent::_toHtml();
    }
}

