<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Model\ResourceModel\Blacklist;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Followup\Model\Blacklist', 'Amasty\Followup\Model\ResourceModel\Blacklist');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}