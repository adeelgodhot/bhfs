<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\ResourceModel\Pack;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\PackSales\Table;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Sale extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(Table::TABLE_NAME, Table::ID_COLUMN);
    }
}
