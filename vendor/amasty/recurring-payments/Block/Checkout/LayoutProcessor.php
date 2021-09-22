<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Block\Checkout;

use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class LayoutProcessor
 */
class LayoutProcessor implements LayoutProcessorInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(Session $checkoutSession, QuoteValidate $quoteValidate, ArrayManager $arrayManager)
    {
        $this->checkoutSession = $checkoutSession;
        $this->quoteValidate = $quoteValidate;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout)
    {
        $items = $this->checkoutSession->getQuote()->getItems();
        $result = [];

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($items as $item) {
            $result[$item->getId()] = (int)$this->quoteValidate->validateQuoteItem($item);
        }

        $jsLayout = $this->arrayManager->merge(
            $this->arrayManager->findPath(
                'amasty_recurring_payments_label',
                $jsLayout,
                'components',
                'children'
            ),
            $jsLayout,
            ['recurring_items' => $result]
        );

        return $jsLayout;
    }
}
