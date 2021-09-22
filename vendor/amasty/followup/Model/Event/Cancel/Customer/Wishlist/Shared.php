<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Cancel\Customer\Wishlist;

class Shared extends \Amasty\Followup\Model\Event\Basic
{
    public function validate($history)
    {
        $collection = $this->_objectManager
            ->create('Magento\Wishlist\Model\ResourceModel\Wishlist\Collection')
            ->addFieldToFilter('customer_id', array('eq' => $history->getCustomerId()))
            ->addFieldToFilter('shared', array('gt' => 0))
            ->addFieldToFilter('updated_at', array('gt' => $history->getCreatedAt()));

        return $collection->getSize() > 0;
    }
}
