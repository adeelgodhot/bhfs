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

class AddPriceColumn
{
    const PRICE = 'product_price';

    public function execute(SchemaSetupInterface $setup): void
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('wishlist_item'),
            self::PRICE,
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '20,6',
                'nullable' => true,
                'comment' => 'Price amount'
            ]
        );
    }
}
