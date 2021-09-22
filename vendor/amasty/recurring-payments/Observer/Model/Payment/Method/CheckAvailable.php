<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Observer\Model\Payment\Method;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Amasty\RecurringPayments\Model\Payment;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Model\Quote;

class CheckAvailable implements ObserverInterface
{
    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var Payment
     */
    private $payment;

    public function __construct(QuoteValidate $quoteValidate, Payment $payment)
    {
        $this->quoteValidate = $quoteValidate;
        $this->payment = $payment;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        if ($quote = $observer->getData('quote')) {
            if ($this->quoteValidate->validateQuote($quote)) {
                /** @var AbstractMethod $methodInstance */
                $methodInstance = $observer->getData('method_instance');
                $allowedMethods = $this->payment->getAllowedMethods($quote->getGrandTotal() < 0.0001);
                /** @var DataObject $result */
                $result = $observer->getData('result');

                if (!$allowedMethods) {
                    $result->setData('is_available', false);
                }

                if ($result->getData('is_available')
                    && $methodInstance->getCode()
                    && in_array($methodInstance->getCode(), $allowedMethods)
                ) {
                    $result->setData('is_available', true);
                } else {
                    $result->setData('is_available', false);
                }
            }
        }
    }
}
