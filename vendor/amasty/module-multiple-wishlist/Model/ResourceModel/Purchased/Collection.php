<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Model\ResourceModel\Purchased;

use Amasty\MWishlist\Model\ResourceModel\Purchased;
use Magento\Framework\DataObject;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(DataObject::class, Purchased::class);
    }
}
