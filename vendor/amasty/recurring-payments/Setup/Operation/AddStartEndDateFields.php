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

class AddStartEndDateFields
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
            SubscriptionInterface::START_DATE,
            [
                'type' => Table::TYPE_TIMESTAMP,
                'nullable' => true,
                'comment' => 'Subscription Start Date (Including Trial)',
            ]
        );

        $connection->addColumn(
            $table,
            SubscriptionInterface::END_DATE,
            [
                'type' => Table::TYPE_TIMESTAMP,
                'nullable' => true,
                'comment' => 'Subscription End Date',
            ]
        );
    }
}
