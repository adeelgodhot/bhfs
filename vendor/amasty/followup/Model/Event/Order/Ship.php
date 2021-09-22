<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Order;

class Ship extends \Amasty\Followup\Model\Event\Order\Status
{
    protected $_statusKey = self::STATUS_KEY_SHIP_ORDER;

    /**
     * @inheritdoc
     */
    protected function addFilters($collection)
    {
        $collection->getSelect()->joinInner(
            ['shipment' => $collection->getTable('sales_shipment')],
            'order.entity_id = shipment.order_id',
            []
        );

        $this->addDateRange($collection, 'shipment.created_at');
    }
}
