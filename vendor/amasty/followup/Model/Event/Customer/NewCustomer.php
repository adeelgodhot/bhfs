<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Customer;

class NewCustomer extends \Amasty\Followup\Model\Event\Basic
{
    public function validate($customer)
    {
        $validateBasic = $this->_validateBasic(
            $customer->getStoreId(),
            $customer->getEmail(),
            $customer->getGroupId()
        );

        return $validateBasic && $customer->getEntityId() === NULL;
    }
}
