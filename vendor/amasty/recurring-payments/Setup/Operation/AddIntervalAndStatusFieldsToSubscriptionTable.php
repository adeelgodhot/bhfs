<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Subscription as SubscriptionResource;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddIntervalAndStatusFieldsToSubscriptionTable
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
            SubscriptionInterface::STATUS,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Status',
                'default' => null
            ]
        );

        $connection->addColumn(
            $table,
            SubscriptionInterface::FREQUENCY,
            [
                'type' => Table::TYPE_INTEGER,
                'length' => null,
                'nullable' => true,
                'comment' => 'Frequency',
                'default' => null
            ]
        );
        $connection->addColumn(
            $table,
            SubscriptionInterface::FREQUENCY_UNIT,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'comment' => 'Frequency Unit',
                'default' => null
            ]
        );
        $connection->addColumn(
            $table,
            SubscriptionInterface::LAST_PAYMENT_DATE,
            [
                'type' => Table::TYPE_TIMESTAMP,
                'nullable' => true,
                'comment' => 'Last Payment Date',
            ]
        );
    }
}
