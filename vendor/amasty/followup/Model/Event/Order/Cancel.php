<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Order;

class Cancel extends \Amasty\Followup\Model\Event\Order\Status
{
    protected $_statusKey = self::STATUS_KEY_CANCEL_ORDER;

    /**
     * @inheritdoc
     */
    protected function addFilters($collection)
    {
        $collection->addFieldToFilter(
            'order.' . \Magento\Sales\Api\Data\OrderInterface::STATE,
            [
                'eq' => \Magento\Sales\Model\Order::STATE_CANCELED
            ]
        );

        $this->addDateRange($collection, 'order.updated_at');
    }
}
