<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\ResourceModel;

use Amasty\RecurringPaypal\Api\Data\ProductInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PaypalProduct extends AbstractDb
{
    const TABLE_NAME = 'amasty_recurring_payments_paypal_product';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ProductInterface::ID);
    }
}
