<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;

class Index extends \Amasty\Followup\Controller\Adminhtml\Queue
{
    public function execute()
    {
        $indexer = $this->_objectManager
            ->create('Amasty\Followup\Model\Indexer');
        $indexer->run();

        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Queue'));
        return $resultPage;

    }
}
