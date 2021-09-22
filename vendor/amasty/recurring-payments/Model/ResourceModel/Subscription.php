<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\ResourceModel;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Subscription extends AbstractDb
{
    const TABLE_NAME = 'amasty_recurring_payments_subscription';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, SubscriptionInterface::ID);
    }
}
