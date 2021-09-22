<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Block\Frontend\Recipe;

class RecipeList extends \Mageside\Recipe\Block\Frontend\AbstractBlock
{
    /**
     * @var \Mageside\Recipe\Model\ResourceModel\Recipe\Collection
     */
    protected $_recipeCollectionFactory;

    /**
     * @var \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\CollectionFactory
     */
    protected $_filterCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /** @var  */
    protected $_collection;

    /** @var  */
    public $_helper;

    /**
     * RecipeList constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mageside\Recipe\Model\FileUploader $fileUploader
     * @param \Mageside\Recipe\Helper\Config $helper
     * @param \Mageside\Recipe\Model\ResourceModel\Recipe\CollectionFactory $recipeFactory
     * @param \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\CollectionFactory $filterCollectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mageside\Recipe\Model\FileUploader $fileUploader,
        \Mageside\Recipe\Helper\Config $helper,
        \Mageside\Recipe\Model\ResourceModel\Recipe\CollectionFactory $recipeFactory,
        \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\CollectionFactory $filterCollectionFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_recipeCollectionFactory = $recipeFactory;
        $this->_filterCollectionFactory = $filterCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_helper = $helper;
        parent::__construct($context, $fileUploader, $helper);
    }

    /**
     * @return mixed
     */
    public function getProductId()
    {
        $product = $this->_coreRegistry->registry('current_product');
        if ($product) {
            return $product->getEntityId();
        } elseif ($productId = $this->_request->getParam('productId')) {
            return $productId;
        }

        return false;
    }

    /**
     * @return \Mageside\Recipe\Model\ResourceModel\Recipe\Collection
     */
    public function getAvailableRecipeCollection()
    {
        if (!$this->_collection) {
            $params = $this->_request->getParams();
            if (isset($params['page']) && is_numeric($params['page'])) {
                $page = $params['page'];
            } else {
                $page = null;
                $this->_request->setParam('page', 1);
            }

            $filters = $this->prepareFilters();
            /** @var \Mageside\Recipe\Model\ResourceModel\Recipe\Collection $recipes */
            $recipes = $this->_recipeCollectionFactory->create();
            $writer = $this->_coreRegistry->registry('writer');

            if ($productId = $this->getProductId()) {
                if (!empty($filters)) {
                    $recipes->applySelectedFilters($filters);
                }
                $recipes->setPageSize($this->_helper->getRecipesPerProductPage())->setCurPage($page ? $page : 1);
            } else {
                if (!empty($filters)) {
                    $recipes->applySelectedFilters($filters);
                }

                if ($writer) {
                    $recipes->addFieldToFilter('customer_id', $writer->getCustomerId());
                }

                if ($search = $this->getSearchParam()) {
                    $recipes->applySearchKeywordFilter($search);
                }

                $recipes
                    ->addStoreFilter()
                    ->addIsEnableFilter()
                    ->addOrder('recipe_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
                    ->setPageSize($this->_helper->getRecipesPerPage())->setCurPage($page ? $page : 1);
            }

            $this->_collection = $recipes;
        }

        return $this->_collection;
    }

    /**
     * @return int
     */
    public function getCollectionSize()
    {
        $collection = $this->getAvailableRecipeCollection();
        return $collection->getSize();
    }

    /**
     * @return array
     */
    public function prepareFilters()
    {
        if ($productId = $this->getProductId()) {
                $filters['product_id'] = $productId;
        } else {
            $filters = [];
            $params = $this->_request->getParams();
            if (!empty($params)) {
                $filterCollection = $this->_filterCollectionFactory->create();
                foreach ($filterCollection->getItems() as $filter) {
                    if (isset($params[$filter->getCode()])) {
                        $filters[$filter->getCode()] = $params[$filter->getCode()];
                    }
                }
            }
        }

        return $filters;
    }

    /**
     * @return mixed
     */
    private function getSearchParam()
    {
        return trim($this->_request->getParam('search', ''));
    }

    /**
     * @param $thumbnail
     * @return string
     */
    public function getRecipeThumbnail($thumbnail)
    {
        return $this->getImageUrl() . DIRECTORY_SEPARATOR . $thumbnail;
    }
}
