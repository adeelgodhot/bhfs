<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShippingCalculator
 */


namespace Amasty\ShippingCalculator\Controller\Estimate;

use Amasty\ShippingCalculator\Model\ProductShippingEstimateService;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Psr\Log\LoggerInterface;

class Ajax extends Action
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressInterfaceFactory;

    /**
     * @var ProductShippingEstimateService
     */
    private $productShippingEstimateService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Ajax constructor.
     *
     * @param Context                                    $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface                 $productRepository
     * @param \Magento\Framework\Registry                $registry
     * @param AddressInterfaceFactory                    $addressInterfaceFactory
     * @param ProductShippingEstimateService             $productShippingEstimateService
     * @param LoggerInterface                            $logger
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry,
        AddressInterfaceFactory $addressInterfaceFactory,
        ProductShippingEstimateService $productShippingEstimateService,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->productShippingEstimateService = $productShippingEstimateService;
        $this->logger = $logger;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $product = $this->_initProduct();
        $shippingMethods = [];
        if ($product) {
            try {
                $params = $this->getRequest()->getParams();
                /** @var \Magento\Quote\Api\Data\AddressInterface $address */
                $address = $this->addressInterfaceFactory->create()
                    ->setCountryId($this->getRequest()->getParam('country_id'))
                    ->setPostcode($this->getRequest()->getParam('postcode'))
                    ->setRegionId($this->getRequest()->getParam('region_id'))
                    ->setRegion($this->getRequest()->getParam('region'));
                $shippingMethods = $this->productShippingEstimateService->estimate($product, $address, $params);
            } catch (\Magento\Framework\Exception\LocalizedException $localizedException) {
                $shippingMethods['error'] = $localizedException->getMessage();
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }
        $this->registry->register('amasty_shipping_rates', $shippingMethods);

        return $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            try {
                $storeId = $this->storeManager->getStore()->getId();
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
}
