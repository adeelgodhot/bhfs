<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\Estimation;

use Amasty\RecurringPayments\Api\Data\EstimationItemInterface;
use Amasty\RecurringPayments\Api\Data\EstimationItemInterfaceFactory;
use Amasty\RecurringPayments\Api\EstimateRecurringPaymentInterface;
use Amasty\RecurringPayments\Model\Generators\QuoteGenerator;
use Amasty\RecurringPayments\Model\QuoteValidate;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Quote\Model\ShippingFactory;

class EstimateRecurringPayment implements EstimateRecurringPaymentInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteValidate
     */
    private $quoteValidate;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var EstimationItemInterfaceFactory
     */
    private $estimationItemFactory;

    /**
     * @var CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var ShippingAssignmentFactory
     */
    private $shippingAssignmentFactory;

    /**
     * @var ShippingFactory
     */
    private $shippingFactory;

    public function __construct(
        CartRepositoryInterface $quoteRepository,
        QuoteValidate $quoteValidate,
        QuoteFactory $quoteFactory,
        EstimationItemInterfaceFactory $estimationItemFactory,
        CartExtensionFactory $cartExtensionFactory,
        ShippingAssignmentFactory $shippingAssignmentFactory,
        ShippingFactory $shippingFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteValidate = $quoteValidate;
        $this->quoteFactory = $quoteFactory;
        $this->estimationItemFactory = $estimationItemFactory;
        $this->cartExtensionFactory = $cartExtensionFactory;
        $this->shippingAssignmentFactory = $shippingAssignmentFactory;
        $this->shippingFactory = $shippingFactory;
    }

    /**
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return \Amasty\RecurringPayments\Api\Data\EstimationItemInterface[]
     */
    public function estimateByShippingInformation(
        $cartId,
        ShippingInformationInterface $addressInformation
    ): array {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $shippingAddress = $addressInformation->getShippingAddress();
        $shippingMethod = null;
        $carrierCode = $addressInformation->getShippingCarrierCode();
        $methodCode = $addressInformation->getShippingMethodCode();

        if ($carrierCode && $methodCode) {
            $shippingAddress->setLimitCarrier($carrierCode);
            $shippingMethod = $carrierCode . '_' . $methodCode;
            $shippingAddress->setShippingMethod($shippingMethod);
        }

        $estimationItems = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$this->quoteValidate->validateQuoteItem($item)) {
                continue;
            }

            $newQuote = $this->generateQuote(
                $item,
                $quote,
                $addressInformation,
                $shippingAddress,
                $shippingMethod
            );

            /** @var EstimationItemInterface $estimationItem */
            $estimationItem = $this->estimationItemFactory->create();
            $estimationItem->setItemId((int)$item->getItemId());
            $estimationItem->setValue((float)$newQuote->getGrandTotal());

            $estimationItems[] = $estimationItem;
        }

        return $estimationItems;
    }

    /**
     * @param Quote\Item $item
     * @param CartInterface $quote
     * @param ShippingInformationInterface $addressInformation
     * @param AddressInterface $shippingAddress
     * @param string|null $shippingMethod
     * @return CartInterface
     */
    private function generateQuote(
        Quote\Item $item,
        CartInterface $quote,
        ShippingInformationInterface $addressInformation,
        \Magento\Quote\Api\Data\AddressInterface $shippingAddress,
        ?string $shippingMethod
    ): CartInterface {
        /** @var Product $product */
        $product = $item->getProduct();
        $product->setData(QuoteGenerator::SUBSCRIPTION_FLAG, true);

        /** @var Quote $newQuote */
        $newQuote = $this->quoteFactory->create();
        $newQuote->setData(QuoteGenerator::GENERATED_FLAG, true);
        $newQuote->setStoreId($quote->getStoreId());

        $paymentMethod = $quote->getPaymentMethod();
        if ($quote->getPayment() && $quote->getPayment()->getMethod()) {
            $paymentMethod = $quote->getPayment()->getMethod();
        }

        $newQuote->setPaymentMethod($paymentMethod);
        $newQuote->addProduct($product, $item->getBuyRequest());

        $billingAddress = $addressInformation->getBillingAddress();
        if ($billingAddress) {
            if (!$billingAddress->getCustomerAddressId()) {
                $billingAddress->setCustomerAddressId(null);
            }
            $newQuote->setBillingAddress($billingAddress);
        }

        $shippingAddress->setAllItems($newQuote->getAllItems());
        $newQuote->setIsMultiShipping(0);
        $shippingAddress->setCollectShippingRates(true);
        $newQuote = $this->prepareShippingAssignment($newQuote, $shippingAddress, $shippingMethod);

        $newQuote->setShippingAddress($shippingAddress);

        $shippingAddress->setCollectShippingRates(true);
        $newQuote->collectTotals();

        return $newQuote;
    }

    /**
     * Prepare shipping assignment.
     *
     * @param CartInterface $quote
     * @param AddressInterface $address
     * @param string|null $method
     * @return CartInterface
     */
    private function prepareShippingAssignment(CartInterface $quote, AddressInterface $address, ?string $method)
    {
        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }

        $shippingAssignments = $cartExtension->getShippingAssignments();
        if (empty($shippingAssignments)) {
            $shippingAssignment = $this->shippingAssignmentFactory->create();
        } else {
            $shippingAssignment = $shippingAssignments[0];
        }

        $shipping = $shippingAssignment->getShipping();
        if ($shipping === null) {
            $shipping = $this->shippingFactory->create();
        }

        $shipping->setAddress($address);
        if ($method) {
            $shipping->setMethod($method);
        }
        $shippingAssignment->setShipping($shipping);
        $cartExtension->setShippingAssignments([$shippingAssignment]);

        return $quote->setExtensionAttributes($cartExtension);
    }
}
