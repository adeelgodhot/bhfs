<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Order;

class Status extends \Amasty\Followup\Model\Event\Basic
{
    protected $_statusKey = null;

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return bool
     */
    public function validate($quote)
    {
        $validateBasic = $this->_validateBasic(
            $quote->getStoreId(),
            $quote->getCustomerEmail(),
            $quote->getCustomerGroupId()
        );

        return $validateBasic && $this->_rule->validateConditions($quote);
    }

    /**
     * @return \Magento\Quote\Model\ResourceModel\Quote\Collection
     */
    protected function _initCollection()
    {
        /** @var \Magento\Quote\Model\ResourceModel\Quote\Collection $collection */
        $collection = $this->_objectManager
            ->create('Magento\Quote\Model\ResourceModel\Quote\Collection');

        $collection->getSelect()->joinInner(
            ['order' => $collection->getTable('sales_order')],
            'main_table.entity_id = order.quote_id',
            [
                'order_id' => 'order.entity_id',
                'increment_id' => 'order.increment_id'
            ]
        );

        switch ($this->_statusKey) {
            case self::STATUS_KEY_CREATE_ORDER:
            case self::STATUS_KEY_INVOICE_ORDER:
            case self::STATUS_KEY_SHIP_ORDER:
            case self::STATUS_KEY_COMPLETE_ORDER:
            case self::STATUS_KEY_CANCEL_ORDER:
                $this->addFilters($collection);
                break;
        }

        $collection->getSelect()->group("main_table.entity_id");

        return $collection;
    }

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote\Collection $collection
     */
    protected function addFilters($collection)
    {
        $this->addDateRange($collection);
    }

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote\Collection $collection
     * @param string $column
     */
    protected function addDateRange($collection, $column = 'order.updated_at')
    {
        $collection->addFieldToFilter(
            $column,
            ['gteq' => $this->_dateTime->formatDate($this->getLastExecuted())]
        );

        $collection->addFieldToFilter(
            $column,
            ['lt' => $this->_dateTime->formatDate($this->getCurrentExecution())]
        );
    }
}
