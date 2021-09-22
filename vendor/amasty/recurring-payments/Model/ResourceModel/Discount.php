<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\ResourceModel;

use Amasty\RecurringPayments\Api\Data\DiscountInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Discount
 */
class Discount extends AbstractDb
{
    const TABLE_NAME = 'amasty_recurring_payments_discount';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, DiscountInterface::ENTITY_ID);
    }
}
