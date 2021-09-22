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

class FillCustomerEmailField
{
    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $subscriptionTableName = $setup->getTable(SubscriptionResource::TABLE_NAME);
        $orderTableName = $setup->getTable('sales_order');

        $customerEmailField = SubscriptionInterface::CUSTOMER_EMAIL;
        $orderIdField = SubscriptionInterface::ORDER_ID;

        $select = $connection->select()
            ->from(
                false,
                [
                    $customerEmailField => new \Zend_Db_Expr('o.customer_email')
                ]
            )->join(
                ['o' => $orderTableName],
                'o.entity_id = ' . $orderIdField,
                []
            );

        $updateQuery = $connection->updateFromSelect(
            $select,
            $subscriptionTableName
        );

        $connection->query($updateQuery);
    }
}
