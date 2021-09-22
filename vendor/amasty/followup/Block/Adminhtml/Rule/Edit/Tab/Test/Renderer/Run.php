<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Block\Adminhtml\Rule\Edit\Tab\Test\Renderer;

class Run extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Amasty\Followup\Helper\Data $helper,
        array $data = []
    ) {
        $this->_helper = $helper;

        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $item)
    {

        $recipientEmail = $this->_helper->getScopeValue('amfollowup/test/recipient');

        $buttonSend = '<button type="button" class="scalable task"
            onclick="amastyFollowupTest.send(' . $item->getId() . ')">
            <span>' . __('Send') .'</span>
            </button><br/><small><i>to '.$recipientEmail.'</i></small>';

        return $buttonSend;
    }
}
