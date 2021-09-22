<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Controller\Adminhtml\Queue;

use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends \Amasty\Followup\Controller\Adminhtml\Queue
{

    public function execute()
    {
        $historyId = (int)$this->getRequest()->getParam('id');

        $history = $this->_objectManager->create('Amasty\Followup\Model\History')
            ->load($historyId);

        if (!$history->getId()) {
            $this->messageManager->addError(__('Something went wrong while editing the queue.'));
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('amasty_followup/*/index');
            return $resultRedirect;
        }

        $this->initCurrentQueue($history);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Followup::followup_rule');
        $this->prepareDefaultCustomerTitle($resultPage);
        $resultPage->setActiveMenu('Amasty_Followup::followup');

        $resultPage->getConfig()->getTitle()->prepend(__('Edit queue item #%1', $historyId));

        return $resultPage;
    }
}
