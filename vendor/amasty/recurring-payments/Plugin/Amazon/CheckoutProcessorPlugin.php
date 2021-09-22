<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Amazon;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Amazon\Payment\Plugin\CheckoutProcessor;
use Magento\Checkout\Model\Session;

/**
 * Class CheckoutProcessorPlugin
 */
class CheckoutProcessorPlugin
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(Session $session, QuoteValidate $quoteValidate)
    {
        $this->session = $session;
        $this->quoteValidate = $quoteValidate;
    }

    /**
     * @param CheckoutProcessor $subject
     * @param array $jsLayout
     *
     * @return array
     */
    public function afterAfterProcess(CheckoutProcessor $subject, array $jsLayout): array
    {
        /** @var \Magento\Quote\Api\Data\CartInterface $quote */
        $quote = $this->session->getQuote();

        if ($this->quoteValidate->validateQuote($quote)) {
            $shippingConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                               ['children']['shippingAddress'];
            $paymentConfig = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                              ['children']['payment'];

            unset($shippingConfig['children']['customer-email']['children']['amazon-button-region']);
            unset($shippingConfig['children']['before-form']['children']['amazon-widget-address']);

            unset($paymentConfig['children']['renders']['children']['amazon_payment']);
            unset($paymentConfig['children']['beforeMethods']['children']['amazon-sandbox-simulator']);
            unset($paymentConfig['children']['payments-list']['children']['amazon_payment-form']);
        }

        return $jsLayout;
    }
}
