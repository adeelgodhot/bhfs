<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Customer;

class Date extends \Amasty\Followup\Model\Event\Basic
{
    public function validate($customer)
    {
        return $this->_validateBasic($customer->getStoreId(), $customer->getEmail(), $customer->getGroupId());
    }

    protected function _initCollection()
    {
        $collection = $this->_objectManager
            ->create('Magento\Customer\Model\ResourceModel\Customer\Collection');

        $collection->addNameToSelect();

        $today = $this->_date->date('Y-m-d');

        $collection->getSelect()->joinInner(
            array('rule' => $collection->getTable('amasty_amfollowup_rule')),
            'rule.rule_id = ' . $this->_rule->getId() . ' and '.
            $collection->getConnection()->quoteInto('rule.customer_date_event = ?', $today),
            array()
        );

        $collection->getSelect()->joinLeft(
            array('history' => $collection->getTable('amasty_amfollowup_history')),
            'e.entity_id = history.customer_id and '.
            'history.rule_id = ' . $this->_rule->getId(),
            array()
        );

        $collection->getSelect()->where("history.history_id is null");

        return $collection;
    }
}
