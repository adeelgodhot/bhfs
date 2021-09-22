<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model\Config\Source;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Customer\Attribute\Source\GroupSourceLoggedInOnlyInterface;
use Magento\Customer\Model\Group;
use Magento\Framework\Convert\DataObject;
use Magento\Persistent\Helper\Data;

class CustomerGroup extends \Magento\Customer\Model\Config\Source\Group
{
    const PERSISTENT_PREFIX = 'persistent_';

    /**
     * @var Data
     */
    private $persistentData;

    public function __construct(
        GroupManagementInterface $groupManagement,
        DataObject $converter,
        Data $persistentData,
        GroupSourceLoggedInOnlyInterface $groupSourceForLoggedInCustomers = null
    ) {
        parent::__construct($groupManagement, $converter, $groupSourceForLoggedInCustomers);
        $this->persistentData = $persistentData;
    }

    public function toOptionArray()
    {
        $result = parent::toOptionArray();
        array_shift($result);

        if ($this->persistentData->isEnabled()) {
            foreach ($result as $customerGroupOption) {
                $result[] = [
                    'value' => self::PERSISTENT_PREFIX . $customerGroupOption['value'],
                    'label' => $customerGroupOption['label'] . __(' (Persistent)')
                ];
            }
        }

        array_unshift($result, [
            'value' => Group::NOT_LOGGED_IN_ID,
            'label' => __('NOT LOGGED IN')
        ]);

        return $result;
    }
}
