<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event\Customer;

class Group extends \Amasty\Followup\Model\Event\Basic
{
    public function _validateCustomerGroupChanged($afterCustomer, $customerGroupsIds, $origValidate = false)
    {
        $arrCustomerGroups = explode(',', $customerGroupsIds);

        $newData = $afterCustomer->getData();
        $newCustGroup = $afterCustomer->getGroupId();
        $origData = $afterCustomer->getOrigData();
        if (!$origData) {
            $afterCustomer->load($afterCustomer->getId());
            $origData = $afterCustomer->getOrigData();
            $afterCustomer->setData($newData);
        }

        $origValidated = TRUE;

        if ($origValidate) {
            $origValidated = is_array($origData) && isset($origData["group_id"]) &&
                $origData["group_id"] != $newCustGroup;
        }

        $validateCustomer = (in_array($afterCustomer->getGroupId(), $arrCustomerGroups) || empty($customerGroupsIds));

        return $origValidated && $validateCustomer;
    }

    public function validate($afterCustomer)
    {
        $validateBasic = $this->_validateBasic(
            $afterCustomer->getStoreId(),
            $afterCustomer->getEmail(),
            $afterCustomer->getGroupId()
        );

        $validateCustomer = $this->_validateCustomerGroupChanged($afterCustomer, $this->_rule->getCustGroups(), true);

        return $validateBasic && $validateCustomer;
    }
}
