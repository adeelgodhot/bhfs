<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\ResourceModel\Discount;

use Amasty\RecurringPayments\Model\Discount;
use Amasty\RecurringPayments\Model\ResourceModel\Discount as DiscountResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(Discount::class, DiscountResource::class);
    }
}
