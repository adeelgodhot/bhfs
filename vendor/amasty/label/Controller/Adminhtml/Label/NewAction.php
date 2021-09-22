<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends Action implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = Edit::ADMIN_RESOURCE;

    /**
     * @return Forward
     */
    public function execute()
    {
        /** @var Forward $forward **/
        $forward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $forward->setModule('amasty_label');
        $forward->setController('label');

        return $forward->forward('edit');
    }
}
