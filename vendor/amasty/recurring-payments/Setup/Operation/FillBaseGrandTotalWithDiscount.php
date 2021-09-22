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

class FillBaseGrandTotalWithDiscount
{
    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $subscriptionTableName = $setup->getTable(SubscriptionResource::TABLE_NAME);

        $field = SubscriptionInterface::BASE_GRAND_TOTAL_WITH_DISCOUNT;
        $grandTotalField = SubscriptionInterface::BASE_GRAND_TOTAL;
        $discountField = SubscriptionInterface::BASE_DISCOUNT_AMOUNT;

        $select = $connection->select()
            ->from(
                false,
                [
                    $field => new \Zend_Db_Expr("`{$grandTotalField}` - `{$discountField}`")
                ]
            );

        $updateQuery = $connection->updateFromSelect(
            $select,
            $subscriptionTableName
        );

        $connection->query($updateQuery);
    }
}
