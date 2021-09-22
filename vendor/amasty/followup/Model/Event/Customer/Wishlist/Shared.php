<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Customer\Wishlist;

class Shared extends \Amasty\Followup\Model\Event\Basic
{
    function validate($customer)
    {
        $wishlist = $this->_objectManager
            ->create('Magento\Wishlist\Model\Wishlist')
            ->loadByCustomerId($customer->getId());

        $validateBasic = $this->_validateBasic(
            $customer->getStoreId(),
            $customer->getEmail(),
            $customer->getGroupId()
        );

        return $wishlist->getItemsCount() > 0 && $validateBasic;
    }

}
