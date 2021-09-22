<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreatePlansTable
{
    /**
     * @param SchemaSetupInterface $installer
     */
    public function execute(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'amasty_recurring_payments_subscription_plan'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(SubscriptionPlan::TABLE_NAME)
        )->addColumn(
            SubscriptionPlanInterface::PLAN_ID,
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Subscription Plan Id'
        )->addColumn(
            SubscriptionPlanInterface::CREATED_AT,
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            SubscriptionPlanInterface::NAME,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => ''],
            'Plan Name'
        )->addColumn(
            SubscriptionPlanInterface::STATUS,
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Status'
        )->addColumn(
            SubscriptionPlanInterface::FREQUENCY,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Frequency'
        )->addColumn(
            SubscriptionPlanInterface::FREQUENCY_UNIT,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            ['nullable' => false, 'default' => ''],
            'Frequency Unit'
        )->addColumn(
            SubscriptionPlanInterface::ENABLE_TRIAL,
            Table::TYPE_BOOLEAN,
            null,
            [
                'nullable' => false
            ],
            'Is Trial Enabled'
        )->addColumn(
            SubscriptionPlanInterface::TRIAL_DAYS,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Number of Trial Days'
        )->addColumn(
            SubscriptionPlanInterface::ENABLE_INITIAL_FEE,
            Table::TYPE_BOOLEAN,
            null,
            [
                'nullable' => false
            ],
            'Is Initial Fee Enabled'
        )->addColumn(
            SubscriptionPlanInterface::INITIAL_FEE_TYPE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Type of Initial Fee'
        )->addColumn(
            SubscriptionPlanInterface::INITIAL_FEE_AMOUNT,
            Table::TYPE_DECIMAL,
            null,
            ['nullable' => false, 'precision' => '12', 'scale' => '4'],
            'Initial Fee Amount'
        )->addColumn(
            SubscriptionPlanInterface::ENABLE_DISCOUNT,
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Is Discount Enabled'
        )->addColumn(
            SubscriptionPlanInterface::DISCOUNT_TYPE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Type of Discount'
        )->addColumn(
            SubscriptionPlanInterface::DISCOUNT_AMOUNT,
            Table::TYPE_DECIMAL,
            null,
            ['nullable' => false, 'precision' => '12', 'scale' => '4'],
            'Discount Amount'
        )->addColumn(
            SubscriptionPlanInterface::ENABLE_DISCOUNT_LIMIT,
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false],
            'Is Discount Limit Enabled'
        )->addColumn(
            SubscriptionPlanInterface::NUMBER_DISCOUNT_CYCLES,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Number of Discount Cycles'
        )->addIndex(
            $installer->getIdxName(SubscriptionPlan::TABLE_NAME, [SubscriptionPlanInterface::PLAN_ID]),
            [SubscriptionPlanInterface::PLAN_ID]
        )->setComment(
            'Amasty Subscription Plan Table'
        );
        $installer->getConnection()->createTable($table);
    }
}
