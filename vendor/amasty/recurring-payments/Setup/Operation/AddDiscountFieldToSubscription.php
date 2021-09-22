<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Subscription as SubscriptionResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddDiscountFieldToSubscription
{
    /**
     * @param SchemaSetupInterface $installer
     */
    public function execute(SchemaSetupInterface $installer)
    {
        $table = $installer->getTable(SubscriptionResource::TABLE_NAME);

        $connection = $installer->getConnection();

        $connection->addColumn(
            $table,
            SubscriptionInterface::BASE_GRAND_TOTAL_WITH_DISCOUNT,
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => null,
                'nullable' => false,
                'comment' => 'Discounted Base Grand Total',
                'precision' => '12',
                'scale' => '4'
            ]
        );
    }
}
