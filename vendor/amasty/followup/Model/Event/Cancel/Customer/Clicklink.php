<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Cancel\Customer;

class Clicklink extends \Amasty\Followup\Model\Event\Basic
{
    public function validate($history)
    {
        $collectionLink = $this->_objectManager
            ->create('Amasty\Followup\Model\ResourceModel\Link\Collection')
            ->addFieldToFilter('customer_id', array('eq' => $history->getCustomerId()))
            ->addFieldToFilter('created_at', array('gt' => $history->getCreatedAt()));

        $collectionHistoryLink = $this->_objectManager
            ->create('Amasty\Followup\Model\ResourceModel\Link\Collection')
            ->addHistoryData()
            ->addFieldToFilter('schedule_id', array('eq' => $history->getScheduleId()))
            ->addFieldToFilter('main_table.customer_id', array('null' => true))
            ->addFieldToFilter('main_table.created_at', array('gt' => $history->getCreatedAt()));

        return $collectionLink->getSize() > 0 || $collectionHistoryLink->getSize() > 0;
    }
}
