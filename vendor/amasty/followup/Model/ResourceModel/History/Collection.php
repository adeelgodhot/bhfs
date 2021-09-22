<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Model\ResourceModel\History;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Followup\Model\History', 'Amasty\Followup\Model\ResourceModel\History');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function addRuleData()
    {
        $this->getSelect()
            ->joinLeft(
                ['rule' => $this->getTable('amasty_amfollowup_rule')],
                'main_table.rule_id = rule.rule_id',
                ['name']
            )
            ->joinLeft(
                ['schedule' => $this->getTable('amasty_amfollowup_schedule')],
                'main_table.rule_id = schedule.rule_id',
                ['GROUP_CONCAT(`schedule`.`delayed_start`)']
            )
        ->group('main_table.history_id');

        return $this;
    }

    public function addOrderData()
    {
        $this->getSelect()
            ->joinLeft(
            array('order' => $this->getTable('sales_order')),
            'main_table.order_id = order.entity_id',
            array('order_status' => 'order.status')
        );
        return $this;
    }

    public function addReadyFilter($date)
    {
        $this->addFieldToFilter('main_table.scheduled_at', array('lteq' => $date));
        $this->addPendingStatusFilter();
        return $this;
    }

    public function addPendingStatusFilter($cond = 'eq')
    {
        $this->addFieldToFilter('main_table.status', array($cond => \Amasty\Followup\Model\History::STATUS_PENDING));
        return $this;
    }

    /**
     * @param $orderId
     * @param $scheduleId
     * @param $ruleId
     * @param $storeId
     * @return $this
     */
    public function getOrderFilter($orderId, $scheduleId, $ruleId, $storeId)
    {
        $notAllowedStatuses = [
            \Amasty\Followup\Model\History::STATUS_PENDING,
            \Amasty\Followup\Model\History::STATUS_SENT,
            \Amasty\Followup\Model\History::STATUS_NO_PRODUCT,
            \Amasty\Followup\Model\History::STATUS_NO_CROSSEL_PRODUCT
        ];

        $this->addFieldToFilter('main_table.status', ['in' => $notAllowedStatuses])
            ->addFieldToFilter('main_table.order_id', ['eq' => $orderId])
            ->addFieldToFilter('main_table.rule_id', ['eq' => $ruleId])
            ->addFieldToFilter('main_table.schedule_id', ['eq' => $scheduleId])
            ->addFieldToFilter('main_table.store_id', ['eq' => $storeId]);

        return $this;
    }
}