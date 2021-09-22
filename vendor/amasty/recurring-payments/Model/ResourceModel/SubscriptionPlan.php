<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\ResourceModel;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SubscriptionPlan extends AbstractDb
{
    const TABLE_NAME = 'amasty_recurring_payments_subscription_plan';

    /**
     * init table name and id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(static::TABLE_NAME, SubscriptionPlanInterface::PLAN_ID);
    }
}
