<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Plugin\Payment\Model\Checks;

use Amasty\RecurringPayments\Model\Payment;
use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

class ZeroTotal
{
    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var Payment
     */
    private $paymentConfig;

    public function __construct(
        Payment $paymentConfig,
        QuoteValidate $quoteValidate
    ) {
        $this->quoteValidate = $quoteValidate;
        $this->paymentConfig = $paymentConfig;
    }

    public function afterIsApplicable(
        \Magento\Payment\Model\Checks\ZeroTotal $subject,
        $result,
        MethodInterface $paymentMethod,
        Quote $quote
    ) {
        if (!$result) {
            if (!in_array(
                $paymentMethod->getCode(),
                $this->paymentConfig->getAllowedMethods($quote->getGrandTotal() < 0.0001)
            )) {
                return $result;
            }

            return $this->quoteValidate->validateQuote($quote);
        }

        return $result;
    }
}
