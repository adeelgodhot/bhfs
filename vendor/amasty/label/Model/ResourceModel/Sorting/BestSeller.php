<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\ResourceModel\Sorting;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class BestSeller extends AbstractDb
{
    const TABLE_NAME = 'amasty_sorting_bestsellers';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'product_id');
    }

    public function getBestSellerPositionSelect(): ?Select
    {
        if ($this->getConnection()->isTableExists($this->getMainTable())) {
            return $this->getConnection()->select()
                ->from(
                    $this->getMainTable(),
                    [
                        'product_id',
                        new \Zend_Db_Expr('@curRow := @curRow + 1 AS bestseller_position')
                    ]
                )
                ->order('qty_ordered DESC')
                ->join(['init' => new \Zend_Db_Expr('(SELECT @curRow := 0)')], '', []);
        }
        
        return null;
    }
}
