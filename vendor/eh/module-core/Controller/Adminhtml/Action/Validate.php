<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2020 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Controller\Adminhtml\Action;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action;
use EH\Core\Cron\GetUpdates;

/**
 * Class Validate
 * @package EH\Core\Controller\Adminhtml\Action
 */
class Validate extends AbstractAction
{
    /**
     * @var GetUpdates
     */
    protected $fetchUpdates;

    /**
     * Validate constructor.
     * @param Action\Context $context
     * @param GetUpdates $getUpdates
     */
    public function __construct(
        Action\Context $context,
        GetUpdates $getUpdates
    ) {
        $this->fetchUpdates = $getUpdates;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $isAjax = $this->getRequest()->isAjax();
        $force = $isAjax ? false : true;

        $this->fetchUpdates->execute($force);
        if (!$isAjax) {
            $this->messageManager->getMessages()->clear();
            $this->_redirect('adminhtml/dashboard/index');
        }
    }
}
