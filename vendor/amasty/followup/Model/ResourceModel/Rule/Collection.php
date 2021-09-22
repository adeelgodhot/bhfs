<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Model\ResourceModel\Rule;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Followup\Model\Rule', 'Amasty\Followup\Model\ResourceModel\Rule');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function addStartFilter($types = array())
    {
        $this->addFilter('is_active', \Amasty\Followup\Model\Rule::RULE_ACTIVE);
        $this->addFieldToFilter('start_event_type', array('in' => $types));
        return $this;
    }
}