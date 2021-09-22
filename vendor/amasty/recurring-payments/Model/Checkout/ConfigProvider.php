<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Checkout;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class ConfigProvider implements ConfigProviderInterface
{

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(CheckoutSession $checkoutSession, QuoteValidate $quoteValidate)
    {
        $this->checkoutSession = $checkoutSession;
        $this->quoteValidate = $quoteValidate;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'amastyRecurringConfig' => [
                'isRecurringProducts' => $this->quoteValidate->validateQuote($this->checkoutSession->getQuote())
            ]
        ];
    }
}
