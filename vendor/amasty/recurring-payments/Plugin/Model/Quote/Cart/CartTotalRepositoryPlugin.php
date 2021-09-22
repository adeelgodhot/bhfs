<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Plugin\Model\Quote\Cart;

use Amasty\RecurringPayments\Model\Generators\QuoteGenerator;
use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;

class CartTotalRepositoryPlugin
{
    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var QuoteGenerator
     */
    private $quoteGenerator;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Quote\Api\Data\TotalsItemExtensionInterfaceFactory
     */
    private $extensionFactory;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        QuoteValidate $quoteValidate,
        \Magento\Quote\Api\Data\TotalsItemExtensionInterfaceFactory $extensionFactory,
        QuoteGenerator $quoteGenerator
    ) {
        $this->quoteValidate = $quoteValidate;
        $this->quoteGenerator = $quoteGenerator;
        $this->quoteRepository = $quoteRepository;
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param CartTotalRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\TotalsInterface $result
     * @param int $cartId
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function afterGet(CartTotalRepositoryInterface $subject, $result, $cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        foreach ($result->getItems() as $item) {
            $quoteItem = $quote->getItemById($item->getItemId());
            if ($this->quoteValidate->validateQuoteItem($quoteItem)) {
                try {
                    /** @var \Magento\Quote\Model\Quote $estimationQuote */
                    $estimationQuote = $this->quoteGenerator->generateFromItem($quoteItem);
                } catch (LocalizedException $e) {
                    continue;
                }
                $extensionAttributes = $item->getExtensionAttributes() ?: $this->extensionFactory->create();
                $extensionAttributes->setAmastyRecurrentEstimate((float)$estimationQuote->getGrandTotal());
                $item->setExtensionAttributes($extensionAttributes);
            }
        }

        return $result;
    }
}
