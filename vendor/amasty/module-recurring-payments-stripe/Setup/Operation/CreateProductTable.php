<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Setup\Operation;

use Amasty\RecurringStripe\Api\Data\ProductInterface;
use Amasty\RecurringStripe\Model\ResourceModel\StripeProduct;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class CreateProductTable
{
    /**
     * @param SchemaSetupInterface $installer
     */
    public function execute(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'amasty_recurring_payments_stripe_product'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(StripeProduct::TABLE_NAME))
            ->addColumn(
                ProductInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                ProductInterface::PRODUCT_ID,
                Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => true],
                'Product Id'
            )
            ->addColumn(
                ProductInterface::STRIPE_PRODUCT_ID,
                Table::TYPE_TEXT,
                255,
                [],
                'Stripe Product Id'
            )
            ->addColumn(
                ProductInterface::STRIPE_ACCOUNT_ID,
                Table::TYPE_TEXT,
                255,
                [],
                'Stripe Account Id of Business Owner'
            )
            ->addForeignKey(
                $installer->getFkName(
                    StripeProduct::TABLE_NAME,
                    ProductInterface::PRODUCT_ID,
                    'catalog_product_entity',
                    'entity_id'
                ),
                ProductInterface::PRODUCT_ID,
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addIndex(
                $installer->getIdxName(
                    StripeProduct::TABLE_NAME,
                    [
                        ProductInterface::PRODUCT_ID,
                        ProductInterface::STRIPE_ACCOUNT_ID
                    ]
                ),
                [ProductInterface::PRODUCT_ID, ProductInterface::STRIPE_ACCOUNT_ID]
            )
            ->setComment('Amasty Recurring Payments Stripe Products');

        $installer->getConnection()->createTable($table);
    }
}
