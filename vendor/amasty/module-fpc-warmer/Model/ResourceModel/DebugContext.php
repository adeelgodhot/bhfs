<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\ResourceModel;

use Amasty\Fpc\Setup\Operation\CreateContextDebugTable;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DebugContext extends AbstractDb
{
    public function _construct()
    {
        $this->_init(CreateContextDebugTable::TABLE_NAME, 'id');
    }

    public function flush()
    {
        $this->getConnection()->truncateTable($this->getMainTable());
    }
}
