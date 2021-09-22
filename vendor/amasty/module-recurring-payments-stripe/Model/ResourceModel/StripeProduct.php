<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\ResourceModel;

use Amasty\RecurringStripe\Api\Data\ProductInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StripeProduct extends AbstractDb
{
    const TABLE_NAME = 'amasty_recurring_payments_stripe_product';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ProductInterface::ENTITY_ID);
    }
}
