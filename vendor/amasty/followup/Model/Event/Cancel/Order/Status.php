<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Cancel\Order;

use \Magento\Sales\Model\Order as SalesOrder;

class Status extends \Amasty\Followup\Model\Event\Basic
{
    public function validate($history)
    {
        $collection = $this->_objectManager
            ->create(\Magento\Sales\Model\ResourceModel\Order\Collection::class);
        $collection->addAttributeToFilter('main_table.entity_id', $history->getOrderId());

        $status = $this->_status->getStatus();
        $historyCreatedAt = $history->getCreatedAt();

        switch ($status) {
            case SalesOrder::STATE_PROCESSING:
                $collection = $this->addFilterForProcessing($collection, $historyCreatedAt);
                break;
            case SalesOrder::STATE_COMPLETE:
                $collection = $this->addFilterForInvoice($collection, $historyCreatedAt);
                $collection = $this->addFilterForShip($collection, $historyCreatedAt);
                break;
            case SalesOrder::STATE_CANCELED:
                $collection = $this->addFilterForCanceled($collection, $historyCreatedAt);
                break;
            case SalesOrder::STATE_CLOSED:
                $collection = $this->addFilterForClosed($collection, $historyCreatedAt);
                break;

        }

        return $collection->getSize() > 0;
    }

    protected function addFilterForProcessing($collection, $historyCreatedAt)
    {
        $collection->getSelect()->joinLeft(
            ['invoice' => $collection->getTable('sales_invoice')],
            'main_table.entity_id = invoice.order_id',
            []
        );

        $collection->getSelect()->joinLeft(
            ['shipment' => $collection->getTable('sales_shipment')],
            'main_table.entity_id = shipment.order_id',
            []
        );

        $collection->addFieldToFilter(
            ['invoice.created_at', 'shipment.created_at'],
            [
                ['gteq' => $historyCreatedAt],
                ['gteq' => $historyCreatedAt]
            ]
        );

        return $collection;
    }

    protected function addFilterForInvoice($collection, $historyCreatedAt)
    {
        $collection->getSelect()->joinInner(
            ['invoice' => $collection->getTable('sales_invoice')],
            'main_table.entity_id = invoice.order_id',
            []
        );

        $collection->addFieldToFilter('invoice.created_at', ['gteq' => $historyCreatedAt]);

        return $collection;
    }

    protected function addFilterForShip($collection, $historyCreatedAt)
    {
        $collection->getSelect()->joinInner(
            ['shipment' => $collection->getTable('sales_shipment')],
            'main_table.entity_id = shipment.order_id',
            []
        );

        $collection->addFieldToFilter('shipment.created_at', ['gteq' => $historyCreatedAt]);

        return $collection;
    }

    protected function addFilterForCanceled($collection, $historyCreatedAt)
    {
        $collection->addFieldToFilter('main_table.status', ['eq' => 'canceled']);
        $collection->addFieldToFilter('main_table.updated_at', ['gteq' => $historyCreatedAt]);

        return $collection;
    }

    protected function addFilterForClosed($collection, $historyCreatedAt)
    {
        $collection->getSelect()->joinInner(
            ['creditmemo' => $collection->getTable('sales_creditmemo')],
            'main_table.entity_id = creditmemo.order_id',
            []
        );

        $collection->addFieldToFilter('creditmemo.created_at', ['gteq' => $historyCreatedAt]);

        return $collection;
    }
}
