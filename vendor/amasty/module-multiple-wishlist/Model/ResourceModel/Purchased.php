<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Model\ResourceModel;

class Purchased extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const QUOTE_ITEM_ID = 'quote_item_id';

    const TABLE_NAME = 'amasty_wishlist_most_purchased';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'product_id');
    }

    public function saveItems(array $items): void
    {
        $this->getConnection()->insertMultiple($this->getMainTable(), $items);
    }
}
