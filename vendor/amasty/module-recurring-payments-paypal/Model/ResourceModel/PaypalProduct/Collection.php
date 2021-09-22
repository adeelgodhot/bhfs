<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\ResourceModel\PaypalProduct;

use Amasty\RecurringPaypal\Model\PaypalProduct;
use Amasty\RecurringPaypal\Model\ResourceModel\PaypalProduct as PaypalProductResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(PaypalProduct::class, PaypalProductResource::class);
    }
}
