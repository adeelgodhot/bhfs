<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Amazon\Gateway;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Amazon\Core\Helper\Data;
use Magento\Checkout\Model\Session;

/**
 * Class ConfigPlugin
 */
class ConfigPlugin
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
     * @param Data $subject
     * @param bool $result
     *
     * @return bool
     */
    public function afterIsActive(\Amazon\Payment\Gateway\Config\Config $subject, bool $result): bool
    {
        if ($result) {
            /** @var \Magento\Quote\Api\Data\CartInterface $quote */
            $quote = $this->session->getQuote();

            $result = !$this->quoteValidate->validateQuote($quote);
        }

        return $result;
    }
}
