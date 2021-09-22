<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Controller\Adminhtml\Plan;

use Magento\Backend\App\Action;

class NewAction extends Action
{
    const ADMIN_RESOURCE = 'Amasty_RecurringPayments::recurring_payments_subscription_plans';

    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
