<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Plugin\Catalog\Controller\Adminhtml\Product\Action\Attribute;

use Amasty\Paction\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\TierPrice as TierPriceBlock;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute;
use Magento\Catalog\Model\Config\Source\ProductPriceOptionsInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Stdlib\ArrayManager;

class Save
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductTierPriceInterfaceFactory
     */
    private $productTierPriceInterfaceFactory;

    /**
     * @var ProductTierPriceExtensionFactory
     */
    private $productTierPriceExtensionFactory;

    /**
     * @var Attribute
     */
    private $attributeHelper;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    public function __construct(
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        ProductTierPriceInterfaceFactory $productTierPriceInterfaceFactory,
        ProductTierPriceExtensionFactory $productTierPriceExtensionFactory,
        Attribute $attributeHelper,
        ArrayManager $arrayManager
    ) {
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->productTierPriceInterfaceFactory = $productTierPriceInterfaceFactory;
        $this->productTierPriceExtensionFactory = $productTierPriceExtensionFactory;
        $this->attributeHelper = $attributeHelper;
        $this->arrayManager = $arrayManager;
    }

    public function beforeExecute(\Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save $subject): void
    {
        $productIds = $this->attributeHelper->getProductIds();
        $requestParams = $this->request->getParams();
        $attrData = $requestParams['attributes'] ?? [];
        $isNeedDeletePrices = $this->request->getParam(TierPriceBlock::TIER_PRICE_CHANGE_CHECKBOX_NAME);
        $newTierPrices = $this->arrayManager->get(TierPrice::PRICE_CODE, $attrData)
            ? $this->prepareTierPrices($attrData[TierPrice::PRICE_CODE])
            : [];

        if ($newTierPrices || $isNeedDeletePrices) {
            foreach ($productIds as $productId) {
                $product = $this->productRepository->getById($productId);
                $product->setMediaGalleryEntries($product->getMediaGalleryEntries());
                $productTierPrices = $isNeedDeletePrices
                    ? $newTierPrices
                    // phpcs:ignore
                    : array_merge($product->getTierPrices(), $newTierPrices);
                $product->setTierPrices($productTierPrices);
                $this->productRepository->save($product);
            }

            unset($attrData[TierPrice::PRICE_CODE]);
        }

        $requestParams['attributes'] = $attrData;
        $this->request->setParams($requestParams);
    }

    private function prepareTierPrices(array $tierPriceDataArray): array
    {
        $result = [];

        foreach ($tierPriceDataArray as $item) {
            if (!$item['price_qty']) {
                continue;
            }

            $tierPriceExtensionAttribute = $this->productTierPriceExtensionFactory->create()
                ->setWebsiteId($item['website_id']);

            if ($isPercentValue = $item['value_type'] === ProductPriceOptionsInterface::VALUE_PERCENT) {
                $tierPriceExtensionAttribute->setPercentageValue($item['price']);
            }

            $key = implode(
                '-',
                [$item['website_id'], $item['cust_group'], (int)$item['price_qty']]
            );
            $result[$key] = $this->productTierPriceInterfaceFactory
                ->create()
                ->setCustomerGroupId($item['cust_group'])
                ->setQty($item['price_qty'])
                ->setValue(!$isPercentValue ? $item['price'] : '')
                ->setExtensionAttributes($tierPriceExtensionAttribute);
        }

        return array_values($result);
    }
}
