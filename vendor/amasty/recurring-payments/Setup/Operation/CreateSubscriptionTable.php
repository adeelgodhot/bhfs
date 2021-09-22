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

class CreateSubscriptionTable
{
    public function execute(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable(SubscriptionResource::TABLE_NAME))
            ->addColumn(
                SubscriptionInterface::ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                SubscriptionInterface::CREATED_AT,
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false, 'default' => Table::TIMESTAMP_INIT
                ]
            )
            ->addColumn(
                SubscriptionInterface::SUBSCRIPTION_ID,
                Table::TYPE_TEXT,
                128,
                ['nullable' => false]
            )
            ->addColumn(
                SubscriptionInterface::ORDER_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SubscriptionInterface::PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SubscriptionInterface::QTY,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SubscriptionInterface::CUSTOMER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true, 'unsigned' => true
                ]
            )
            ->addColumn(
                SubscriptionInterface::PAYMENT_METHOD,
                Table::TYPE_TEXT,
                50,
                [
                    'nullable' => false
                ]
            )
            ->addColumn(
                SubscriptionInterface::ADDRESS_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true, 'unsigned' => true
                ]
            )
            ->addColumn(
                SubscriptionInterface::PRODUCT_OPTIONS,
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false
                ]
            )
            ->addColumn(
                SubscriptionInterface::STORE_ID,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false, 'unsigned' => true
                ]
            )
            ->addColumn(
                SubscriptionInterface::SHIPPING_METHOD,
                Table::TYPE_TEXT,
                128,
                [
                    'nullable' => false
                ]
            )
            ->addColumn(
                SubscriptionInterface::INITIAL_FEE,
                Table::TYPE_DECIMAL,
                null,
                ['nullable' => false, 'precision' => '12', 'scale' => '2']
            )
            ->addColumn(
                SubscriptionInterface::BASE_DISCOUNT_AMOUNT,
                Table::TYPE_DECIMAL,
                null,
                ['nullable' => false, 'precision' => '12', 'scale' => '2']
            )
            ->addColumn(
                SubscriptionInterface::BASE_GRAND_TOTAL,
                Table::TYPE_DECIMAL,
                null,
                ['nullable' => false, 'precision' => '12', 'scale' => '2']
            )
            ->addColumn(
                SubscriptionInterface::FREE_SHIPPING,
                Table::TYPE_BOOLEAN,
                null,
                [
                    'nullable' => false
                ]
            )
            ->addColumn(
                SubscriptionInterface::TRIAL_DAYS,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                SubscriptionInterface::DELIVERY,
                Table::TYPE_TEXT,
                128,
                ['nullable' => false]
            )
            ->addColumn(
                SubscriptionInterface::REMAINING_DISCOUNT_CYCLES,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true]
            )
            ->addForeignKey(
                $installer->getFkName(
                    SubscriptionResource::TABLE_NAME,
                    SubscriptionInterface::PRODUCT_ID,
                    'catalog_product_entity',
                    'entity_id'
                ),
                SubscriptionInterface::PRODUCT_ID,
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    SubscriptionResource::TABLE_NAME,
                    SubscriptionInterface::STORE_ID,
                    'store',
                    'store_id'
                ),
                SubscriptionInterface::STORE_ID,
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    SubscriptionResource::TABLE_NAME,
                    SubscriptionInterface::CUSTOMER_ID,
                    'customer_entity',
                    'entity_id'
                ),
                SubscriptionInterface::CUSTOMER_ID,
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    SubscriptionResource::TABLE_NAME,
                    SubscriptionInterface::ADDRESS_ID,
                    'amasty_recurring_payments_address',
                    'entity_id'
                ),
                SubscriptionInterface::ADDRESS_ID,
                $installer->getTable('amasty_recurring_payments_address'),
                'entity_id',
                Table::ACTION_SET_NULL
            )
            ->addIndex(
                $installer->getIdxName(
                    SubscriptionResource::TABLE_NAME,
                    [SubscriptionInterface::SUBSCRIPTION_ID]
                ),
                [SubscriptionInterface::SUBSCRIPTION_ID]
            )
            ->setComment('Amasty Recurring Payments Subscriptions Table');

        $installer->getConnection()->createTable($table);
    }
}
