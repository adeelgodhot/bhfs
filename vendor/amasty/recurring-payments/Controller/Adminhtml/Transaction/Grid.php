<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Controller\Adminhtml\Transaction;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Grid
 */
class Grid extends Action
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_RecurringPayments::recurring_payments_transaction_log');
        $resultPage->getConfig()->getTitle()->prepend(__('Transactions Log'));

        return $resultPage;
    }

    /**
     * Check the permission to watch transaction's log
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_RecurringPayments::recurring_payments');
    }
}
