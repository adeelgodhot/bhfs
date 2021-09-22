<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\ResourceModel\Transaction;

use Amasty\RecurringPayments\Model\ResourceModel\Transaction as TransactionResource;
use Amasty\RecurringPayments\Model\Transaction;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(Transaction::class, TransactionResource::class);
    }
}
