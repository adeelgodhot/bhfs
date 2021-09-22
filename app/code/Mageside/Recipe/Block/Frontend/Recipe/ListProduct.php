<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Block\Frontend\Recipe;

use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * Class ListProduct
 * @package Mageside\Recipe\Block\Frontend\Recipe
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * Product Collection
     *
     * @var AbstractCollection
     */
    protected $productCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var
     */
    protected $collectionFilter;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $productVisibility;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * ListProduct constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
        $this->productCollectionFactory = $productCollectionFactory;
        $this->registry = $context->getRegistry();
        $this->productVisibility = $productVisibility;
        $this->catalogConfig = $context->getCatalogConfig();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|AbstractCollection
     */
    protected function _getProductCollection()
    {
        if ($this->productCollection === null) {
            $this->productCollection = $this->initializeProductCollection();
        }

        return $this->productCollection;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function initializeProductCollection()
    {
        $collection = $this->productCollectionFactory->create();

        $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addFieldToFilter(
                'entity_id',
                ['in' => $this->getCurrentRecipe()->getAssignedProductIds()]
            )
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds());

        return $collection;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCurrentRecipe()
    {
        $recipe = $this->getData('recipe');
        if ($recipe === null) {
            $recipe = $this->registry->registry('recipe');
            if ($recipe) {
                $this->setData('recipe', $recipe);
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Recipe not found.')
                );
            }
        }

        return $recipe;
    }

}
