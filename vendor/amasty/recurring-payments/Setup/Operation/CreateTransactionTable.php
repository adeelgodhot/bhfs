<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Data\TransactionInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Transaction;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class CreateTransactionTable
 */
class CreateTransactionTable
{
    /**
     * @param SchemaSetupInterface $installer
     */
    public function execute(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'amasty_recurring_payments_transaction_log'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(Transaction::TABLE_NAME))
            ->addColumn(
                TransactionInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                TransactionInterface::TRANSACTION_ID,
                Table::TYPE_TEXT,
                100,
                ['nullable' => true],
                'Transaction Id'
            )
            ->addColumn(
                TransactionInterface::ORDER_ID,
                Table::TYPE_TEXT,
                32,
                ['nullable' => true],
                'Order Id'
            )
            ->addColumn(
                TransactionInterface::BILLING_AMOUNT,
                Table::TYPE_DECIMAL,
                null,
                ['nullable' => false, 'precision' => '12', 'scale' => '2'],
                'Billing Amount'
            )
            ->addColumn(
                TransactionInterface::BILLING_DATE,
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Billing Date'
            )
            ->addColumn(
                TransactionInterface::STATUS,
                Table::TYPE_INTEGER,
                1,
                ['nullable' => false, 'default' => 0],
                'Status'
            )
            ->addColumn(
                TransactionInterface::CURRENCY_CODE,
                Table::TYPE_TEXT,
                32,
                ['nullable' => false],
                'Currency Code'
            )
            ->setComment('Amasty Recurring Payments Transaction Log Table');

        $installer->getConnection()->createTable($table);
    }
}
