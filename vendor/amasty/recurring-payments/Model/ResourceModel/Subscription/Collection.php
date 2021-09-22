<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\ResourceModel\Subscription;

use Amasty\RecurringPayments\Model\ResourceModel\Subscription as SubscriptionResource;
use Amasty\RecurringPayments\Model\Subscription;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(Subscription::class, SubscriptionResource::class);
    }
}
