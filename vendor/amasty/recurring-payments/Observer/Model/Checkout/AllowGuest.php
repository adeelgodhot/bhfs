<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Observer\Model\Checkout;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\Event\{Observer, ObserverInterface};

/**
 * Class AllowGuest
 */
class AllowGuest implements ObserverInterface
{
    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(QuoteValidate $quoteValidate)
    {
        $this->quoteValidate = $quoteValidate;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getData('quote');

        /** @var \Magento\Framework\DataObject $result */
        $result = $observer->getData('result');

        if ($result->getData('is_allowed')) {
            $result->setData('is_allowed', !$this->quoteValidate->validateQuote($quote));
        }
    }
}
