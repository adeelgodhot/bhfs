<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Block\Adminhtml\Queue\Edit;

use Amasty\Followup\Controller\RegistryConstants;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected $_coreRegistry = null;
    protected $_helper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        \Amasty\Followup\Helper\Data $helper,
        array $data = []
    ) {

        $this->setId('queue_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Queue View'));

        $this->_coreRegistry = $registry;
        $this->_helper = $helper;

        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }
}