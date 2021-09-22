<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Controller\Adminhtml\History;
use Magento\Backend\App\Action;
class Index extends \Amasty\Followup\Controller\Adminhtml\History
{
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('History'));

        return $resultPage;

    }
}
