<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Order;

class Invoice extends \Amasty\Followup\Model\Event\Order\Status
{
    protected $_statusKey = self::STATUS_KEY_INVOICE_ORDER;

    /**
     * @inheritdoc
     */
    protected function addFilters($collection)
    {
        $collection->getSelect()->joinInner(
            ['invoice' => $collection->getTable('sales_invoice')],
            'order.entity_id = invoice.order_id',
            []
        );

        $this->addDateRange($collection, 'invoice.created_at');
    }
}
