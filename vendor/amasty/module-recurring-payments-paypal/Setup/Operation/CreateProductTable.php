<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Setup\Operation;

use Amasty\RecurringPaypal\Api\Data\ProductInterface;
use Amasty\RecurringPaypal\Model\ResourceModel\PaypalProduct;
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
         * Create table 'amasty_recurring_payments_paypal_product'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(PaypalProduct::TABLE_NAME))
            ->addColumn(
                ProductInterface::ID,
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
                ProductInterface::PAYPAL_PRODUCT_ID,
                Table::TYPE_TEXT,
                50, // According to https://developer.paypal.com/docs/api/catalog-products/v1/
                [],
                'Paypal Product Id'
            )
            ->addForeignKey(
                $installer->getFkName(
                    PaypalProduct::TABLE_NAME,
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
                    PaypalProduct::TABLE_NAME,
                    [ProductInterface::PRODUCT_ID]
                ),
                [ProductInterface::PRODUCT_ID]
            )
            ->setComment('Amasty Recurring Payments Paypal Products');

        $installer->getConnection()->createTable($table);
    }
}
