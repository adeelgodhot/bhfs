<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Model\SalesRule\Condition;

class Combine extends \Magento\SalesRule\Model\Rule\Condition\Combine
{
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress,
        array $data = []
    ) {

        parent::__construct($context, $eventManager, $conditionAddress, $data);

        $this->setType('Amasty\Followup\Model\SalesRule\Condition\Combine');
    }

}