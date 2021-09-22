<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'amasty_amfollowup_rule'
         */

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_amfollowup_rule'))
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Id'
            )
            ->addColumn(
                'start_event_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Start Event Type'
            )
            ->addColumn(
                'cancel_event_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Cancel Event Type'
            )
            ->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Name'
            )
            ->addColumn(
                'is_active',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 1],
                'Is active'
            )
            ->addColumn(
                'to_subscribers',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'To Subscribers'
            )
            ->addColumn(
                'stores',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Stores'
            )
            ->addColumn(
                'cust_groups',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [ 'nullable' => false],
                'Customer Groups'
            )
            ->addColumn(
                'segments',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true,],
                'Segments'
            )
            ->addColumn(
                'sender_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Sender Name'
            )
            ->addColumn(
                'sender_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Sender Email'
            )
            ->addColumn(
                'sender_cc',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Sender CC'
            )
            ->addColumn(
                'utm_source',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Utm Source'
            )
            ->addColumn(
                'utm_medium',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Utm Medium'
            )
            ->addColumn(
                'utm_term',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Utm Term'
            )
            ->addColumn(
                'utm_content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Utm Content'
            )
            ->addColumn(
                'utm_campaign',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Utm Campaign'
            )
            ->addColumn(
                'conditions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Conditions Serialized'
            )
            ->addColumn(
                'customer_date_event',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable' => true],
                'Customer Date Event'
            )
            ->setComment('Amasty Follow Up Rules');
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'amasty_amfollowup_link'
         */

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_amfollowup_link'))
            ->addColumn(
                'link_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Link Id'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Customer Id'
            )
            ->addColumn(
                'history_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'History Id'
            )
            ->addColumn(
                'link',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Link'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addForeignKey(
                $installer->getFkName('amasty_amfollowup_link', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Amasty Follow Up Link');
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'amasty_amfollowup_blacklist'
         */

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_amfollowup_blacklist'))
            ->addColumn(
                'blacklist_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Blacklist Id'
            )
            ->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Email'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addIndex(
                $installer->getIdxName(
                    'amasty_amfollowup_blacklist',
                    ['email'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['email'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment('Amasty Follow Up Blacklist');
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'amasty_amfollowup_attribute'
         */

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_amfollowup_attribute'))
            ->addColumn(
                'attr_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Attribute Id'
            )
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false,],
                'Rule Id'
            )
            ->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Email'
            )
            ->addForeignKey(
                $installer->getFkName('amasty_amfollowup_attribute', 'rule_id', 'amasty_amfollowup_rule', 'rule_id'),
                'rule_id',
                $installer->getTable('amasty_amfollowup_rule'),
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Amasty Follow Up Attribute');
        $installer->getConnection()->createTable($table);



        /**
         * Create table 'amasty_amfollowup_schedule'
         */

        $table = $installer->getConnection()
            ->newTable($installer->getTable('amasty_amfollowup_schedule'))
            ->addColumn(
                'schedule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Schedule Id'
            )
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Rule ID'
            )
            ->addColumn(
                'email_template_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Email Template ID'
            )
            ->addColumn(
                'delayed_start',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Delayed Start'
            )
            ->addColumn(
                'coupon_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                [],
                'Coupon Type'
            )
            ->addColumn(
                'discount_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Discount Amount'
            )
            ->addColumn(
                'expired_in_days',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Expired In Days'
            )
            ->addColumn(
                'discount_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Discount Qty'
            )
            ->addColumn(
                'discount_step',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Discount Step'
            )
            ->addColumn(
                'promo_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => true],
                'Promo Sku'
            )
            ->addColumn(
                'promo_cats',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'nullable' => true],
                'Promo Categoies'
            )
            ->addColumn(
                'ampromo_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => true, 'default' => 0],
                'Ampromo Type'
            )
            ->addColumn(
                'subtotal_greater_than',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12, 4],
                [],
                'Subtotal Greater Than'
            )
            ->addColumn(
                'use_rule',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['default' => false, 'nullable' => false],
                'Use Rule'
            )
            ->addColumn(
                'sales_rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Sales Rule Id'
            )
            ->addForeignKey(
                $installer->getFkName('amasty_amfollowup_schedule', 'rule_id', 'amasty_amfollowup_rule', 'rule_id'),
                'rule_id',
                $installer->getTable('amasty_amfollowup_rule'),
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('amasty_amfollowup_schedule', 'email_template_id', 'email_template', 'template_id'),
                'email_template_id',
                $installer->getTable('email_template'),
                'template_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->setComment('Amasty Follow Up Schedule');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'amasty_amfollowup_history'
         */

        $table = $installer->getConnection()->newTable($installer->getTable('amasty_amfollowup_history'))
            ->addColumn(
                'history_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'History Id'
            )->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Order Id'
            )
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Rule ID'
            )
            ->addColumn(
                'schedule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Schedule ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => true,],
                'Store Id'
            )
            ->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Email'
            )
            ->addColumn(
                'increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Increment Id'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Customer Id'
            )
            ->addColumn(
                'customer_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Customer_Name'
            )
            ->addColumn(
                'body',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '1M',
                [],
                'Email Body'
            )
            ->addColumn(
                'subject',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Email Subject'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'scheduled_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Scheduled At'
            )
            ->addColumn(
                'executed_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Executed Date'
            )->addColumn(
                'finished_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true],
                'Finished Date'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => true],
                'Status'
            )
            ->addColumn(
                'reason',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => true],
                'Reason'
            )
            ->addColumn(
                'public_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => true],
                'Public Key'
            )
            ->addColumn(
                'sales_rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Sales Rule ID'
            )
            ->addColumn(
                'coupon_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Coupon Code'
            )
            ->addColumn(
                'coupon_to_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable' => true],
                'Coupon To Date'
            )
            ->addColumn(
                'coupon_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Coupon Id'
            )
            ->addForeignKey(
                $installer->getFkName('amasty_amfollowup_history', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('amasty_amfollowup_history', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('amasty_amfollowup_history', 'order_id', 'sales_order', 'entity_id'),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )
            ->addIndex(
                $installer->getIdxName(
                    'amasty_amfollowup_history',
                    ['status'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['status'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->setComment('Amasty Follow Up History');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
