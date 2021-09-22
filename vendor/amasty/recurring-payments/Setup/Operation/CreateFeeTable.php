<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Data\FeeInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Fee;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class CreateFeeTable
 */
class CreateFeeTable
{
    /**
     * @param SchemaSetupInterface $installer
     */
    public function execute(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'amasty_recurring_payments_fee_quote'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(Fee::TABLE_NAME))
            ->addColumn(
                FeeInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                FeeInterface::QUOTE_ID,
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => true],
                'Quote Id'
            )
            ->addColumn(
                FeeInterface::AMOUNT,
                Table::TYPE_DECIMAL,
                null,
                ['nullable' => false, 'precision' => '12', 'scale' => '2'],
                'Amount'
            )
            ->addColumn(
                FeeInterface::BASE_AMOUNT,
                Table::TYPE_DECIMAL,
                null,
                ['nullable' => false, 'precision' => '12', 'scale' => '2'],
                'Base Amount'
            )
            ->addForeignKey(
                $installer->getFkName(
                    Fee::TABLE_NAME,
                    FeeInterface::QUOTE_ID,
                    'quote',
                    'entity_id'
                ),
                FeeInterface::QUOTE_ID,
                $installer->getTable('quote'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Amasty Recurring Payments Fee Table');

        $installer->getConnection()->createTable($table);
    }
}
