<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Generators;

use Amasty\RecurringPayments\Api\Generators\QuoteGeneratorInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;

class QuoteGenerator implements QuoteGeneratorInterface
{
    const SUBSCRIPTION_FLAG = 'amasty_subscription_product';
    const GENERATED_FLAG = 'amasty_subscription_is_generated';
    const DISABLE_DISCOUNT_FLAG = 'amasty_subscription_disable_discount';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CustomerFromOrderAddressConverter
     */
    private $customerFromOrderAddressConverter;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        QuoteFactory $quoteFactory,
        SerializerInterface $serializer,
        CartRepositoryInterface $quoteRepository,
        CustomerFromOrderAddressConverter $customerFromOrderAddressConverter
    ) {
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->quoteFactory = $quoteFactory;
        $this->serializer = $serializer;
        $this->quoteRepository = $quoteRepository;
        $this->customerFromOrderAddressConverter = $customerFromOrderAddressConverter;
    }

    public function generate(
        SubscriptionInterface $subscription,
        ?\Magento\Sales\Api\Data\OrderAddressInterface $orderShippingAddress = null,
        ?\Magento\Sales\Api\Data\OrderAddressInterface $orderBillingAddress = null
    ): CartInterface {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($subscription->getProductId());
        $product->setData(self::SUBSCRIPTION_FLAG, true);

        /** @var Quote $newQuote */
        $newQuote = $this->quoteFactory->create();
        $newQuote->setData(self::GENERATED_FLAG, true);
        $newQuote->setStoreId($subscription->getStoreId());
        /** @var \Magento\Customer\Model\Data\Customer $customer */
        $customer = $this->customerRepository->getById($subscription->getCustomerId());

        $data = [
            'subscription_product' => true,
            'base_discount_amount' => $this->getDiscount($subscription),
            'free_shipping' => (bool)$subscription->getFreeShipping(),
            'qty' => $subscription->getQty()
        ];

        if ($subscription->getProductOptions()) {
            $data += $this->serializer->unserialize($subscription->getProductOptions());
        }

        $request = new \Magento\Framework\DataObject($data);

        $newQuote->addProduct($product, $request);

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $newQuote->getShippingAddress();
        $shippingAddress->setAllItems($newQuote->getAllItems());
        $newQuote->getBillingAddress()->setAllItems($newQuote->getAllItems());
        $newQuote->setIsActive(true);

        $quoteShippingAddress = $quoteBillingAddress = null;
        if ($orderShippingAddress) {
            $customerShippingAddress = $this->customerFromOrderAddressConverter->convert($orderShippingAddress);
            $quoteShippingAddress = $shippingAddress->importCustomerAddressData($customerShippingAddress);
        }

        if ($orderBillingAddress) {
            $customerBillingAddress = $this->customerFromOrderAddressConverter->convert($orderBillingAddress);
            $quoteBillingAddress = $newQuote->getBillingAddress()->importCustomerAddressData($customerBillingAddress);
        }
        $newQuote->assignCustomerWithAddressChange(
            $customer,
            $quoteBillingAddress,
            $quoteShippingAddress
        );
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->setShippingMethod($subscription->getShippingMethod());
        $newQuote->setPaymentMethod($subscription->getPaymentMethod());

        // Compatibility with cash on delivery fee
        // because for invoices and creditmemos that fee working based on quote_id
        $this->quoteRepository->save($newQuote);
        $newQuote->setTotalsCollectedFlag(false);
        $newQuote->collectTotals();

        return $newQuote;
    }

    /**
     * @inheritDoc
     */
    public function generateFromItem(CartItemInterface $item, bool $disableDiscount = false): CartInterface
    {
        /** @var Quote $oldQuote */
        $oldQuote = $item->getQuote();
        /** @var Product $product */
        $product = $item->getProduct();
        $product->setData(self::SUBSCRIPTION_FLAG, true);

        /** @var Quote $newQuote */
        $newQuote = $this->quoteFactory->create();
        if ($disableDiscount) {
            $newQuote->setData(self::DISABLE_DISCOUNT_FLAG, true);
        }
        $newQuote->setData(self::GENERATED_FLAG, true);
        $newQuote->setStoreId($oldQuote->getStoreId());

        $paymentMethod = $oldQuote->getPaymentMethod();
        if ($oldQuote->getPayment() && $oldQuote->getPayment()->getMethod()) {
            $paymentMethod = $oldQuote->getPayment()->getMethod();
        }

        $newQuote->setPaymentMethod($paymentMethod);
        $customer = $oldQuote->getCustomer();
        $newQuote->addProduct($product, $item->getBuyRequest());
        $oldShipping = $oldQuote->getShippingAddress();
        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $newQuote->getShippingAddress();
        $shippingAddress->setAllItems($newQuote->getAllItems());
        $newQuote->getBillingAddress()->setAllItems($newQuote->getAllItems());
        $newQuote->setIsActive(true);
        $newQuote->assignCustomer($customer);
        $shippingAddress->setCollectShippingRates(true);
        $shippingAddress->setShippingMethod($oldQuote->getShippingAddress()->getShippingMethod());
        $this->populateAddress($shippingAddress, $oldShipping);
        $this->populateAddress($newQuote->getBillingAddress(), $oldQuote->getBillingAddress());
        $newQuote->collectTotals();

        return $newQuote;
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $toAddress
     * @param \Magento\Quote\Api\Data\AddressInterface $fromAddress
     */
    private function populateAddress(
        \Magento\Quote\Api\Data\AddressInterface $toAddress,
        \Magento\Quote\Api\Data\AddressInterface $fromAddress
    ): void {
        $toAddress->setEmail($fromAddress->getEmail());
        $toAddress->setCompany($fromAddress->getCompany());
        $toAddress->setStreet($fromAddress->getStreet());
        $toAddress->setCity($fromAddress->getCity());
        $toAddress->setCountryId($fromAddress->getCountryId());
        $toAddress->setRegion($fromAddress->getRegion());
        $toAddress->setRegionId($fromAddress->getRegionId());
        $toAddress->setRegionCode($fromAddress->getRegionCode());
        $toAddress->setFax($fromAddress->getFax());
        $toAddress->setFirstname($fromAddress->getFirstname());
        $toAddress->setLastname($fromAddress->getLastname());
        $toAddress->setMiddlename($fromAddress->getMiddlename());
        $toAddress->setTelephone($fromAddress->getTelephone());
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return float
     */
    private function getDiscount(SubscriptionInterface $subscription): float
    {
        $baseDiscountAmount = (float)$subscription->getBaseDiscountAmount();
        $cycles = $subscription->getRemainingDiscountCycles();

        // unlimited discount
        if ($cycles === null) {
            return $baseDiscountAmount;
        }

        // discount is end
        if ($cycles <= 0) {
            return 0.0;
        }

        return $baseDiscountAmount;
    }
}
