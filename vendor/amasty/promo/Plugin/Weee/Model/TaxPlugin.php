<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\Weee\Model;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Weee\Model\Tax;

class TaxPlugin
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    public function __construct(Session $checkoutSession, CartRepositoryInterface $quoteRepository)
    {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param Tax $subject
     * @param \Magento\Framework\DataObject[] $result
     * @param Product $product
     * @param null $shipping
     * @param null $billing
     * @param null $website
     * @param null $calculateTax
     * @param bool $round
     * @return array
     */
    public function afterGetProductWeeeAttributes(
        Tax $subject,
        $result,
        $product,
        $shipping = null,
        $billing = null,
        $website = null,
        $calculateTax = null,
        $round = true
    ) {
        try {
            $quote = $this->quoteRepository->get($this->checkoutSession->getQuoteId());

            foreach ($quote->getAllItems() as $item) {
                if ($item->getProductId() == $product->getId() && $item->getAmpromoRuleId() !== null) {
                    $result = [];
                }
            }
            // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
        } catch (NoSuchEntityException $e) {
            // no quota to check
        }

        return $result;
    }
}
