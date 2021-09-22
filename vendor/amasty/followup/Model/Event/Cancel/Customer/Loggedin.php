<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Cancel\Customer;

class Loggedin extends \Amasty\Followup\Model\Event\Basic
{
    public function validate($history)
    {
        $customer = $this->_objectManager
            ->create('Magento\Customer\Model\Customer')
            ->load($history->getCustomerId());
        $logCustomer = $this->_objectManager
            ->create('Magento\Customer\Model\Logger')
            ->get($customer->getId());

        return strtotime($logCustomer->getLastLoginAt()) > strtotime($history->getCreatedAt());

    }
}
