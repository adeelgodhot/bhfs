<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Model\ResourceModel\Schedule;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Followup\Model\Schedule', 'Amasty\Followup\Model\ResourceModel\Schedule');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function addRule($rule)
    {
        $this->addFilter('rule_id', $rule->getId());
        return $this;
    }
}