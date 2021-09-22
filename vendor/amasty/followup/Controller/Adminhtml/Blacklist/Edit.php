<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Controller\Adminhtml\Blacklist;

class Edit extends \Amasty\Followup\Controller\Adminhtml\Blacklist
{
    /**
     * Customer edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $blacklistId = (int)$this->getRequest()->getParam('id');

        $blacklistData = [];

        $blacklist = $this->_objectManager->create('Amasty\Followup\Model\Blacklist');
        $isExistingBlacklist = (bool)$blacklistId;

        if ($isExistingBlacklist) {
            $blacklist = $blacklist->load($blacklistId);

            if (!$blacklist->getId()) {
                $this->messageManager->addError(__('Something went wrong while editing the blacklist.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('amasty_followup/*/index');
                return $resultRedirect;
            }
        }

        $this->initCurrentBlacklist($blacklist);

        $blacklistData['blacklist_id'] = $blacklistId;

        $this->_getSession()->setBlacklistData($blacklistData);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Followup::followup_blacklist');
        $this->prepareDefaultCustomerTitle($resultPage);
        $resultPage->setActiveMenu('Amasty_Followup::followup');
        if ($isExistingBlacklist) {
            $resultPage->getConfig()->getTitle()->prepend($blacklist->getName());
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Blacklist Email'));
        }


        return $resultPage;
    }
}
