<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Setup\Operation;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class AddWishlistColumns
{
    public function execute(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('wishlist'),
            'name',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Wish List Name by Amasty'
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('wishlist'),
            'type',
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'comment' => 'Type of wishlist by Amasty'
            ]
        );
    }
}
