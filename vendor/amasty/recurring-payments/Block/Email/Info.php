<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Block\Email;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Bundle\Model\Product\Type;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\RecurringPayments\Model\Amount;
use Magento\Catalog\Helper\Image;

class Info extends Template
{
    const FIRST_PARENT_PRODUCT = 0;
    const BASE_IMAGE_ID = 'product_base_image';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var Type
     */
    private $bundleType;

    /**
     * @var Grouped
     */
    private $groupedType;

    /**
     * @var Amount
     */
    private $amount;

    /**
     * @var Image
     */
    private $imageHelper;

    public function __construct(
        Template\Context $context,
        ProductRepositoryInterface $productRepository,
        Configurable $configurableType,
        Type $bundleType,
        Grouped $groupedType,
        Amount $amount,
        Image $imageHelper,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
        $this->bundleType = $bundleType;
        $this->groupedType = $groupedType;
        $this->amount = $amount;
        $this->imageHelper = $imageHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return (int)$this->getProduct()->getId();
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return (string)$this->getProduct()->getName();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductUrl(): string
    {
        /** @var ProductInterface $product */
        $product = $this->getProduct();

        if ($product->getVisibility() == Visibility::VISIBILITY_NOT_VISIBLE) {
            $parentProductIds = $this->getParentIdsByChild($this->getProductId());

            if (!empty($parentProductIds[self::FIRST_PARENT_PRODUCT])) {
                /** @var ProductInterface $product */
                $product = $this->productRepository->getById($parentProductIds[self::FIRST_PARENT_PRODUCT]);
            }
        }

        return (string)$product->getUrlModel()->getUrl($product);
    }

    /**
     * @param int $productId
     *
     * @return array
     */
    private function getParentIdsByChild($productId): array
    {
        $parentProductIds = [];

        if ($this->configurableType->getParentIdsByChild($productId)) {
            $parentProductIds = $this->configurableType->getParentIdsByChild($productId);
        } elseif ($this->bundleType->getParentIdsByChild($productId)) {
            $parentProductIds = $this->bundleType->getParentIdsByChild($productId);
        } elseif ($this->groupedType->getParentIdsByChild($productId)) {
            $parentProductIds = $this->groupedType->getParentIdsByChild($productId);
        }

        return $parentProductIds;
    }

    /**
     * @param string $price
     *
     * @return string
     */
    public function getOriginalPrice($price): string
    {
        return (string)$this->amount->convertAndFormat((float)$price);
    }

    /**
     * @return string
     */
    public function getProductImage(): string
    {
        return (string)$this->imageHelper->init($this->getProduct(), self::BASE_IMAGE_ID)->getUrl();
    }
}
