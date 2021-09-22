<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringCashOnDelivery
 */


namespace Amasty\RecurringCashOnDelivery\Plugin\Payment\Checks;

use Amasty\RecurringPayments\Model\Generators\QuoteGenerator;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\Payment\Model\Checks\TotalMinMax;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

class TotalMinMaxPlugin
{

    /**
     * Disable min/max total check for cash on delivery subscription quote
     *
     * @param TotalMinMax $subject
     * @param bool $result
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     */
    public function afterIsApplicable(TotalMinMax $subject, $result, MethodInterface $paymentMethod, Quote $quote)
    {
        if ($paymentMethod->getCode() == Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE
            && $quote->getData(QuoteGenerator::GENERATED_FLAG)
        ) {
            return true;
        }

        return $result;
    }
}
