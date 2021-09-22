<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Customer;

class Birthday extends \Amasty\Followup\Model\Event\Basic
{
    /**
     * @param $customer
     * @return bool
     */
    public function validate($customer)
    {
        return $this->_validateBasic($customer->getStoreId(), $customer->getEmail(), $customer->getGroupId());
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected function _initCollection()
    {

        $days = intVal(
            $this->_helper->getScopeValue(\Amasty\Followup\Helper\Data::CONFIG_PATH_GENERAL_BIRTHDAY_OFFSET)
        ) * 24 * 60 * 60;
        $modifiedDate = (($days > 0) ? $this->getCurrentExecution() - $days : $this->getCurrentExecution());
        $collection = $this->collectionCustomerFactory->create();
        $collection->addNameToSelect();

        $collection->getSelect()->joinLeft(
            array('history' => $collection->getTable('amasty_amfollowup_history')),
            'e.entity_id = history.customer_id and '.
            'history.rule_id = ' . $this->_rule->getId() . ' and '.
            'DATEDIFF(history.created_at, "' . $this->_dateTime->formatDate($this->getCurrentExecution()) . '") = 0',
            array()
        );

        $collection->addExpressionAttributeToSelect('birth_month', 'MONTH({{dob}})', 'dob')
            ->addExpressionAttributeToSelect('birth_day', 'DAY({{dob}})', 'dob')
            ->joinAttribute('dob', 'customer/dob', 'entity_id', null, 'left')
            ->addFieldToFilter('birth_month', array('eq' =>
                $this->_date->date("m", $modifiedDate
                )))
            ->addFieldToFilter('birth_day', array('eq' =>
                $this->_date->date("d", $modifiedDate
                )));

        $collection->getSelect()->where("history.history_id is null");

        return $collection;
    }
}
