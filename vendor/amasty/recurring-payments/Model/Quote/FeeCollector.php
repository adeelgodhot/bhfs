<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Quote;

use Amasty\RecurringPayments\Api\FeeRepositoryInterface;
use Amasty\RecurringPayments\Api\Data\FeeInterface;
use Amasty\RecurringPayments\Model\Amount;
use Amasty\RecurringPayments\Model\Fee;
use Amasty\RecurringPayments\Model\Generators\QuoteGenerator;
use Amasty\RecurringPayments\Model\Product;
use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as MagentoProduct;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Psr\Log\LoggerInterface;

class FeeCollector extends AbstractTotal
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var FeeRepositoryInterface
     */
    private $feeRepository;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var ItemDataRetriever
     */
    private $itemDataRetriever;

    public function __construct(
        LoggerInterface $logger,
        Product $product,
        ProductRepositoryInterface $productRepository,
        FeeRepositoryInterface $feeRepository,
        Amount $amount,
        QuoteValidate $quoteValidate,
        ItemDataRetriever $itemDataRetriever
    ) {
        $this->logger = $logger;
        $this->product = $product;
        $this->productRepository = $productRepository;
        $this->feeRepository = $feeRepository;
        $this->amount = $amount;
        $this->quoteValidate = $quoteValidate;
        $this->itemDataRetriever = $itemDataRetriever;
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        try {
            $total->setBaseTotalAmount($this->getCode(), 0);
            $total->setTotalAmount($this->getCode(), 0);

            if ($quote->getId() === null || $quote->hasData(QuoteGenerator::GENERATED_FLAG)) {
                return $this;
            }

            /** @var Fee $fee */
            $fee = $this->feeRepository->getByQuoteId((int)$quote->getId());

            $initialFeeData = [
                FeeInterface::ENTITY_ID => $fee->getEntityId(),
                FeeInterface::QUOTE_ID => $quote->getId(),
                FeeInterface::BASE_AMOUNT => 0,
                FeeInterface::AMOUNT => 0,
            ];

            $items = $quote->getItems();

            /** @var Item $item */
            foreach ($items as $item) {
                if ($this->quoteValidate->validateQuoteItem($item)) {
                    /** @var MagentoProduct $product */
                    $product = $this->productRepository->getById($item->getProduct()->getId());
                    $plan = $this->itemDataRetriever->getPlan($item, false);

                    if ($this->product->isRecurringEnable($product)
                        && $plan->getEnableInitialFee()
                        && $plan->getInitialFeeAmount()
                    ) {
                        $initialFee = $this->amount->getAmount(
                            $product,
                            (float)$plan->getInitialFeeAmount(),
                            $plan->getInitialFeeType(),
                            $item
                        );
                        $initialFeeData[FeeInterface::BASE_AMOUNT] += $initialFee;
                        $initialFeeData[FeeInterface::AMOUNT] += $this->amount->convertAndRound($initialFee);

                        $total->setBaseTotalAmount($this->getCode(), $initialFeeData[FeeInterface::BASE_AMOUNT]);
                        $total->setTotalAmount($this->getCode(), $initialFeeData[FeeInterface::AMOUNT]);
                    }
                }
            }

            $fee->setData($initialFeeData);
            $this->feeRepository->save($fee);
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }

        return $this;
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param Quote $quote
     * @param Total $total
     *
     * @return array
     * @codingStandardsIgnoreStart
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, Total $total)
    {
        /** @var Fee $fee */
        $fee = $this->feeRepository->getByQuoteId((int)$quote->getId());

        if ($fee && $fee->getAmount()) {
            return [
                'code' => $this->getCode(),
                'title' => __($this->getLabel()),
                'value' => $fee->getAmount()
            ];
        }

        return null;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Initial Subscription Fee');
    }
}
