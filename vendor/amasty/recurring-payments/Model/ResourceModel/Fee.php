<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\ResourceModel;

use Amasty\RecurringPayments\Api\Data\FeeInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Fee
 */
class Fee extends AbstractDb
{
    const TABLE_NAME = 'amasty_recurring_payments_fee_quote';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, FeeInterface::ENTITY_ID);
    }
}
