<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Block\Cart\Additional;

use Magento\Framework\View\Element\Template;
use Amasty\RecurringPayments\Model\QuoteValidate ;

/**
 * Class Info
 */
class Info extends \Magento\Checkout\Block\Cart\Additional\Info
{
    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    public function __construct(Template\Context $context, QuoteValidate $quoteValidate, array $data = [])
    {
        parent::__construct($context, $data);
        $this->quoteValidate = $quoteValidate;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $this->getItem();

        return $this->quoteValidate->validateQuoteItem($item);
    }
}
