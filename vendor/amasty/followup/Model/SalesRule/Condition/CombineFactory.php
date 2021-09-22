<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Model\SalesRule\Condition;

class CombineFactory extends \Magento\SalesRule\Model\Rule\Condition\CombineFactory
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = '\\Amasty\\Followup\\Model\\SalesRule\\Condition\\Combine'
    ) {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }
}