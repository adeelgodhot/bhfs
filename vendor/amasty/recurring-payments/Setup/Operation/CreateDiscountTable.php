<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Setup\Operation;

use Amasty\RecurringPayments\Api\Data\DiscountInterface;
use Amasty\RecurringPayments\Model\ResourceModel\Discount;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class CreateDiscountTable
 */
class CreateDiscountTable
{
    /**
     * @param SchemaSetupInterface $installer
     */
    public function execute(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'amasty_recurring_payments_discount'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(Discount::TABLE_NAME))
            ->addColumn(
                DiscountInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                DiscountInterface::SUBSCRIPTION_ID,
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Subscription Id'
            )
            ->addColumn(
                DiscountInterface::DISCOUNT_USAGE,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Count of Discount Usage'
            )
            ->addColumn(
                DiscountInterface::AVAILABLE_DISCOUNT_USAGE,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Count of Available Discount Usage'
            )
            ->addIndex(
                $installer->getIdxName(Discount::TABLE_NAME, [DiscountInterface::SUBSCRIPTION_ID]),
                [DiscountInterface::SUBSCRIPTION_ID]
            )
            ->setComment('Amasty Recurring Payments Discount Table');

        $installer->getConnection()->createTable($table);
    }
}
