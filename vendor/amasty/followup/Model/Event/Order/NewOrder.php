<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Order;

class NewOrder extends \Amasty\Followup\Model\Event\Order\Status
{
    protected $_statusKey = self::STATUS_KEY_CREATE_ORDER;

    /**
     * @inheritdoc
     */
    protected function addFilters($collection)
    {
        $this->addDateRange($collection, 'order.created_at');
    }
}
