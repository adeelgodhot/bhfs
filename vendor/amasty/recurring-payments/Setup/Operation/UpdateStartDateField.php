<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Subscription as SubscriptionResource;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpdateStartDateField
{
    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $subscriptionTableName = $setup->getTable(SubscriptionResource::TABLE_NAME);

        $startDateField = SubscriptionInterface::START_DATE;
        $createdAtField = SubscriptionInterface::CREATED_AT;

        $connection->update(
            $subscriptionTableName,
            [
                $startDateField => new \Zend_Db_Expr("`{$createdAtField}`")
            ]
        );
    }
}
