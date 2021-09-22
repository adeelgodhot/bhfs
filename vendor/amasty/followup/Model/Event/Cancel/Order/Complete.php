<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Cancel\Order;

class Complete extends \Amasty\Followup\Model\Event\Basic
{
    public function validate($history)
    {
        $collection = $this->_objectManager
            ->create('Magento\Sales\Model\ResourceModel\Order\Collection');

        if ($history->getOrderId()) {
            $collection->addFieldToFilter('entity_id', array('gt' => $history->getOrderId()));
        } else {
            $collection->addFieldToFilter('created_at', array('gteq' => $history->getCreatedAt()));
        }

        if ($history->getCustomerId()) {
            $collection->addFieldToFilter('customer_id', array('eq' => $history->getCustomerId()));
        } else {
            $collection->addFieldToFilter('customer_email', array('eq' => $history->getEmail()));
        }

        return $collection->getSize() > 0;
    }
}
