<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

/**
 * Class TaxItemStorage
 */
class TaxItemStorage
{
    /**
     * storage for item
     *
     * @var \Magento\Quote\Model\Quote\Item|null
     */
    public static $item = null;
}
