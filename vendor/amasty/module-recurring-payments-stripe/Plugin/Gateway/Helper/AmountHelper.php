<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


namespace Amasty\RecurringStripe\Plugin\Gateway\Helper;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Checkout\Model\Session as CheckoutSession;

class AmountHelper
{
    const STRIPE_MIN_AMOUNT = 50;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(
        CheckoutSession $checkoutSession,
        QuoteValidate $quoteValidate
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteValidate = $quoteValidate;
    }

    public function afterGetAmountForStripe(\Amasty\Stripe\Gateway\Helper\AmountHelper $subject, $result)
    {
        $quote = $this->checkoutSession->getQuote();

        if ($quote->getGrandTotal() > 0.0001 || !$this->quoteValidate->validateQuote($quote)) {
            return $result;
        }

        return self::STRIPE_MIN_AMOUNT;
    }
}
