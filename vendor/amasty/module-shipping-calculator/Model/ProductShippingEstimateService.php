<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingCalculator
 */


namespace Amasty\ShippingCalculator\Model;

class ProductShippingEstimateService
{
    /**
     * @var \Magento\Quote\Api\ShippingMethodManagementInterface
     */
    private $shippingMethodManagement;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    private $storeManager;

    /**
     * ProductShippingEstimateService constructor.
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    public function __construct(
        \Magento\Quote\Api\ShippingMethodManagementInterface $shippingMethodManagement,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->shippingMethodManagement = $shippingMethodManagement;
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->localeResolver = $localeResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @param array $addToCartParams
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function estimate(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        \Magento\Quote\Api\Data\AddressInterface $address,
        array $addToCartParams
    ) {
        if (isset($addToCartParams['qty'])) {
            $filter = new \Zend_Filter_LocalizedToNormalized(
                ['locale' => $this->localeResolver->getLocale()]
            );
            $addToCartParams['qty'] = $filter->filter($addToCartParams['qty']);
        }

        $quote = $this->quoteFactory->create();
        $quote->addProduct($product, new \Magento\Framework\DataObject($addToCartParams));
        $quote->setStoreId($this->storeManager->getStore()->getId());
        $this->quoteRepository->save($quote);
        $shippingMethods = $this->shippingMethodManagement->estimateByExtendedAddress($quote->getId(), $address);
        $this->quoteRepository->delete($quote);

        return $shippingMethods;
    }
}
