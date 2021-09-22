<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\ResourceModel\StripeProduct;

use Amasty\RecurringStripe\Model\StripeProduct;
use Amasty\RecurringStripe\Model\ResourceModel\StripeProduct as StripeProductResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(StripeProduct::class, StripeProductResource::class);
    }
}
