<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Data\ScheduleInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Schedule;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateScheduleTable
{
    /**
     * @param SchemaSetupInterface $installer
     */
    public function execute(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'amasty_recurring_payments_schedule'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(Schedule::TABLE_NAME)
        )->addColumn(
            ScheduleInterface::SCHEDULE_ID,
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Schedule Id'
        )->addColumn(
            ScheduleInterface::SUBSCRIPTION_ID,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            ['nullable' => false, 'default' => ''],
            'Subscription'
        )->addColumn(
            ScheduleInterface::JOB_CODE,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            128,
            ['nullable' => false, 'default' => ''],
            'Job code'
        )->addColumn(
            ScheduleInterface::STATUS,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            7,
            ['nullable' => false, 'default' => ScheduleInterface::STATUS_PENDING],
            'Status'
        )->addColumn(
            ScheduleInterface::MESSAGE,
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            [],
            'Message'
        )->addColumn(
            ScheduleInterface::CREATED_AT,
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Created At'
        )->addColumn(
            ScheduleInterface::SCHEDULED_AT,
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Scheduled At'
        )->addColumn(
            ScheduleInterface::EXECUTED_AT,
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Executed At'
        )->addColumn(
            ScheduleInterface::FINISHED_AT,
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => true],
            'Finished At'
        )->addIndex(
            $installer->getIdxName('amasty_recurring_payments_schedule', [ScheduleInterface::SUBSCRIPTION_ID]),
            [ScheduleInterface::SUBSCRIPTION_ID]
        )->addIndex(
            $installer->getIdxName(
                'amasty_recurring_payments_schedule',
                [ScheduleInterface::SCHEDULED_AT, ScheduleInterface::STATUS]
            ),
            [ScheduleInterface::SCHEDULED_AT, ScheduleInterface::STATUS]
        )->setComment(
            'Amasty Subscription Schedule'
        );
        $installer->getConnection()->createTable($table);
    }
}
