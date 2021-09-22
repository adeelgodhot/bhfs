<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Plugin\Model\Quote\Item;

use Amasty\RecurringPayments\Model\Quote\Validator\StartEndDateValidator;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Processor;

class ProcessorPlugin
{
    /**
     * @var StartEndDateValidator
     */
    private $startEndDateValidator;

    public function __construct(StartEndDateValidator $startEndDateValidator)
    {
        $this->startEndDateValidator = $startEndDateValidator;
    }

    /**
     * @param Processor $subject
     * @param void $result
     * @param Item $item
     * @param DataObject $request
     * @param Product $candidate
     * @return void
     */
    public function afterPrepare(Processor $subject, $result, Item $item, DataObject $request, Product $candidate)
    {
        $this->startEndDateValidator->validate($item);
        return $result;
    }
}
