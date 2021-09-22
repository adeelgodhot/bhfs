<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan;

use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan as SubscriptionPlanResource;
use Amasty\RecurringPayments\Model\SubscriptionPlan;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Init data model and resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(SubscriptionPlan::class, SubscriptionPlanResource::class);
    }
}
