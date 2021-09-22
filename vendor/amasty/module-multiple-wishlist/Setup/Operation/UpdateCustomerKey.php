<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;

class UpdateCustomerKey
{
    public function execute(SchemaSetupInterface $setup)
    {
        $wishlistTable = $setup->getTable('wishlist');
        $customerTable = $setup->getTable('customer_entity');

        foreach ($setup->getConnection()->getForeignKeys($wishlistTable) as $foreignKeyName => $foreignKey) {
            if ($foreignKey['COLUMN_NAME'] === 'customer_id') {
                $setup->getConnection()->dropForeignKey($wishlistTable, $foreignKeyName);
            }
        }

        $setup->getConnection()->dropIndex(
            $wishlistTable,
            $setup->getIdxName($wishlistTable, ['customer_id'])
        );

        $setup->getConnection()->addIndex(
            $wishlistTable,
            $setup->getIdxName($wishlistTable, ['customer_id']),
            ['customer_id']
        );

        $setup->getConnection()->addForeignKey(
            $setup->getFkName($wishlistTable, 'customer_id', $customerTable, 'entity_id'),
            $wishlistTable,
            'customer_id',
            $setup->getTable('customer_entity'),
            'entity_id'
        );
    }
}
