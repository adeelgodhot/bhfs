<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Customer;

use Amasty\Followup\Model\History as History;

class Activity extends \Amasty\Followup\Model\Event\Basic
{
    public function validate($customer)
    {
        return $this->_validateBasic($customer->getStoreId(), $customer->getEmail(), $customer->getGroupId());
    }

    protected function _initCollection()
    {
        $winbackPeriod = $this->_helper->getScopeValue('amfollowup/general/winback_period') * 60 * 60 * 24;

        $collection = $this->_objectManager
            ->create('Magento\Customer\Model\ResourceModel\Customer\Collection');

        $collection->addNameToSelect();

        $collection->getSelect()->joinRight(
            array('log' => $collection->getTable('customer_log')),
            'e.entity_id = log.customer_id',
            array()
        );

        $collection->getSelect()->joinLeft(
            array('history_n_canceled' => $collection->getTable('amasty_amfollowup_history')),
            'e.entity_id = history_n_canceled.customer_id and '.
            'history_n_canceled.rule_id = ' . $this->_rule->getId() . ' and ' .
            'history_n_canceled.status <> "' . History::STATUS_CANCEL . '"',
            array()
        );

        $collection->getSelect()->where("history_n_canceled.history_id is null");

        $collection->getSelect()->where(
            'log.last_login_at > ?',
            $this->_dateTime->formatDate($this->getLastExecuted() - $winbackPeriod)
        );

        $collection->getSelect()->group("e.entity_id");

        $collection->getSelect()->having(
            "MAX(log.last_login_at) < '"
            . $this->_dateTime->formatDate((int)$this->getCurrentExecution() - $winbackPeriod)
            . "'"
        );

        return $collection;
    }
}
