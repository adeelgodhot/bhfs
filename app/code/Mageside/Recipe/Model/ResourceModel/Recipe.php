<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\App\RequestInterface;
use Mageside\Recipe\Model\ResourceModel\Recipe\Filter\CollectionFactory;
use Mageside\Recipe\Model\ResourceModel\Recipe\Filter\DataFactory;
use Mageside\Recipe\Model\ResourceModel\RecipeProductFactory;
use Mageside\Recipe\Model\ResourceModel\StoreFactory;
use Mageside\Recipe\Model\FileUploader;
use Mageside\Recipe\Model\ResourceModel\SummaryFactory;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageside\Recipe\Helper\Config;
use Magento\Catalog\Model\Product;
use Magento\Framework\Model\AbstractModel;

class Recipe extends AbstractDb
{
    protected $availableOptions = ['ingredients', 'method'];

    protected $availableImages = ['thumbnail', 'media_type_image'];

    /**
     * @var Recipe\Filter\CollectionFactory
     */
    protected $_filterCollectionFactory;

    /**
     * @var Recipe\Filter\DataFactory
     */
    protected $_resourceFilterDataFactory;

    /**
     * @var RecipeProductFactory
     */
    protected $_resourceAssignedProductFactory;

    /**
     * @var StoreFactory
     */
    protected $_resourceStoreFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Mageside\Recipe\Model\FileUploader
     */
    protected $_fileUploader;

    /**
     * @var SummaryFactory
     */
    protected $_summaryFactory;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Mageside\Recipe\Helper\Config
     */
    protected $_helper;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_productModel;

    /** @var Writer  */
    protected $resourceModelWriter;

    /** @var \Mageside\Recipe\Model\Recipe  */
    protected $recipe;

    /** @var \Magento\Framework\Message\ManagerInterface  */
    protected $_messageManager;

    /**
     * Recipe constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Recipe\Filter\CollectionFactory $filterCollectionFactory
     * @param Recipe\Filter\DataFactory $resourceFilterDataFactory
     * @param RecipeProductFactory $resourceAssignedProductFactory
     * @param StoreFactory $resourceModelStoreF
     * @param \Mageside\Recipe\Model\FileUploader $fileUploader
     * @param SummaryFactory $summaryFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mageside\Recipe\Helper\Config $helper
     * @param \Magento\Catalog\Model\Product $productModel
     * @param Writer $resourceModelWriter
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param null $connectionName
     */
    public function __construct(
        \Mageside\Recipe\Model\ResourceModel\Writer $resourceModelWriter,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Context $context,
        RequestInterface $request,
        CollectionFactory $filterCollectionFactory,
        DataFactory $resourceFilterDataFactory,
        RecipeProductFactory $resourceAssignedProductFactory,
        StoreFactory $resourceModelStoreF,
        FileUploader $fileUploader,
        SummaryFactory $summaryFactory,
        ReviewFactory $reviewFactory,
        StoreManagerInterface $storeManager,
        Config $helper,
        Product $productModel,
        $connectionName = null
    ) {
        $this->_request = $request;
        $this->_filterCollectionFactory = $filterCollectionFactory;
        $this->_resourceFilterDataFactory = $resourceFilterDataFactory;
        $this->_resourceAssignedProductFactory = $resourceAssignedProductFactory;
        $this->_resourceStoreFactory = $resourceModelStoreF;
        $this->_fileUploader = $fileUploader;
        $this->_summaryFactory = $summaryFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
        $this->_productModel = $productModel;
        $this->resourceModelWriter = $resourceModelWriter;
        $this->_messageManager = $messageManager;
        $this->_mainTable='ms_recipe';
        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('ms_recipe', 'recipe_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $recipeUrlKey = $object->getUrlKey();
        $urlKey = isset($recipeUrlKey) ? trim($recipeUrlKey) : false;
        if (!empty($urlKey)) {
            $urlKey = $this->_productModel->formatUrlKey($urlKey);
        } else {
            $urlKey = $this->_productModel->formatUrlKey($object->getTitle());
        }

        $isDuplicateSaved = false;
        $count = 0;
        do {
            $newUrlKey = $urlKey;
            if ($count >= 1) {
                $newUrlKey = $urlKey . '-' . $count;
            }

            if ($this->isUrlKeyExistInRecipes(empty($object->getRecipeId()) ? 0 : $object->getRecipeId(), $newUrlKey)) {
                $isDuplicateSaved = true;
                $object->setUrlKey($newUrlKey);
            }
            $count++;
        } while ((!$isDuplicateSaved) and ($count < 10));

        if ((!$isDuplicateSaved) and ($count >= 10)) {
            $this->_messageManager->addErrorMessage(
                "Url key already exist is some on recipe!!! Please change it in recipe \"
                 {$object->getTitle()} \"!!!"
            );
        }

        foreach ($this->availableOptions as $column) {
            $optionData = [];
            if (!empty($object->getData($column))) {
                foreach ($object->getData($column) as $ingredientOption) {
                    $flag = false;
                    foreach ($ingredientOption as $item => $value) {
                        if (($item!='record_id') && (strlen($value)>1)) {
                            $flag = true;
                        }
                    }
                    if ($flag) {
                        $optionData[] = $ingredientOption;
                    }
                }
            }

            if (!empty($optionData)) {
                $serialize = json_encode($optionData);
            } else {
                $serialize = '';
            }
            $object->setData($column, $serialize);
        }

        $servingsNumber = $object->getServingsNumberFrom() . ' - ' . $object->getServingsNumberTo();
        if ($servingsNumber) {
            unset($object['servings_number_from']);
            unset($object['servings_number_to']);
            $object->setData('servings_number', $servingsNumber);
        }

        foreach ($this->availableImages as $imageName) {
            if (!empty($object[$imageName])) {
                $name = $object[$imageName][0]['name'];
                $this->_fileUploader->moveFileFromTmp($name);
                $object->setData($imageName, $name);
            }
        }

        return parent::_beforeSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveFiltersData($object)
            ->saveStore($object);

        return parent::_afterSave($object);
    }

    private function saveFiltersData($object)
    {
        if ($recipeId = $object->getRecipeId()) {
            /** @var \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Data $resourceFilterData */
            $resourceFilterData = $this->_resourceFilterDataFactory->create();
            $resourceFilterData->clearFiltersById($recipeId);

            $filterData = [];
            $filterCollection = $this->_filterCollectionFactory->create();
            foreach ($filterCollection->getItems() as $column) {
                if ($filter = $object->getData($column->getCode())) {
                    foreach ($filter as $optionsId) {
                        $filterData[] = [
                            'recipe_id' => $recipeId,
                            'filter_id' => $column->getId(),
                            'filter_options_id' => $optionsId
                        ];
                    }
                }
            }

            if (!empty($filterData)) {
                $resourceFilterData->saveFilterData($filterData);
            }
        }

        return $this;
    }

    public function saveAssignedProduct($object, $products)
    {
        if ($recipeId = $object->getRecipeId()) {
            /** @var \Mageside\Recipe\Model\ResourceModel\RecipeProduct $resourceProducts */
            $resourceProducts = $this->_resourceAssignedProductFactory->create();
            $resourceProducts->clearAssignedProduct($recipeId);

            if ($products) {
                $assignedProduct = [];
                foreach ($products as $product) {
                    $assignedProduct[] = [
                        'recipe_id'     => $recipeId,
                        'product_id'    => $product['id'],
                        'qty'   => $product['qty']
                    ];
                }

                if (!empty($assignedProduct)) {
                    $resourceProducts->saveAssignedProduct($assignedProduct);
                }
            }
        }

        return $this;
    }

    private function saveStore($object)
    {
        if ($recipeId = $object->getRecipeId()) {
            /** @var \Mageside\Recipe\Model\ResourceModel\Store $resourceStores */
            $resourceStores = $this->_resourceStoreFactory->create();
            $resourceStores->clearStoresById($recipeId);

            if ($stores = $object->getData('store_view')) {
                $storeData = [];
                if (in_array(0, $stores)) {
                    $storeData[] = [
                        'recipe_id' => $recipeId,
                        'store_id'  => 0
                    ];
                } else {
                    foreach ($stores as $id) {
                        $storeData[] = [
                            'recipe_id' => $recipeId,
                            'store_id'  => $id
                        ];
                    }
                }

                if (!empty($storeData)) {
                    $resourceStores->saveChoosedStore($storeData);
                }
            }
        }

        return $this;
    }

    protected function _afterLoad(AbstractModel $object)
    {
        $this->joinRecipeData($object);

        if ($servingNumber = $object->getServingsNumber()) {
            $numbers = explode('-', $servingNumber);
            $object->setServingsNumberFrom(trim($numbers[0]));
            $object->setServingsNumberTo(trim($numbers[1]));
        }
        if ($ingredients = $object->getIngredients()) {
            $object->setIngredients(json_decode($ingredients, true));
        } else {
            $object->setIngredients([]);
        }

        if ($method = $object->getMethod()) {
            $object->setMethod(json_decode($method, true));
        } else {
            $object->setMethod([]);
        }
        $entityPkValue = $object->getRecipeId();
        $review = $this->_reviewFactory->create();
        $entityId = $review->getEntityIdByCode(\Mageside\Recipe\Model\Review::RECIPE_CODE);
        $storeId = $this->_request->getParam('store');
        if (!$storeId) {
            $storeId = 0;
        }
        $summaryData = $this->_summaryFactory
            ->create()
            ->getSummaryByEntityId($entityPkValue, $entityId, $storeId);
        $object->setRatingSummary($summaryData);

        parent::_afterLoad($object);
    }

    /**
     * Get store identifier
     *
     * @return  int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param $object
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Select_Exception
     */
    public function joinRecipeData($object)
    {
        $currentRecipeId = $object->getData('recipe_id');

        if (($this->_request->getParam('store') != null)) {
            $currentStoreId = $this->_request->getParam('store');
        } elseif ($this->getStoreId() != null) {
            $currentStoreId = $this->getStoreId();
        } else {
            $currentStoreId = '0';
        }

        if ($currentRecipeId) {
            $connection = $this->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            $selects = [];

            foreach (['text', 'varchar'] as $dateType) {
                $selects[] = $connection->select()
                    ->from(["attrtable"=>$this->getTable("ms_recipe_{$dateType}")])
                    ->reset(\Zend_Db_Select::COLUMNS)
                    ->joinLeft(
                        ["store_attrtable"=>$this->getTable("ms_recipe_{$dateType}")],
                        "attrtable.meta_key = store_attrtable.meta_key
                        and store_attrtable.recipe_id = " . strval($currentRecipeId) . "
                         and store_attrtable.store_id = " . $currentStoreId,
                        null
                    )
                    ->where("attrtable.recipe_id = " . strval($currentRecipeId) . " and attrtable.store_id = 0")
                    ->columns([
                        "meta_key" => "attrtable.meta_key",
                        "meta_value" => new \Zend_Db_Expr(
                            "IFNULL(store_attrtable.meta_value, attrtable.meta_value)"
                        ),
                        "is_default" => new \Zend_Db_Expr(
                            "if(store_attrtable.meta_value IS NULL, 1, 0)"
                        ),
                        "default_value" => "attrtable.meta_value"
                    ]);
            }
            $attribUnion=$connection->select()->union($selects, \Magento\Framework\DB\Select::SQL_UNION_ALL);
            $data=$connection->fetchAll($attribUnion);
            $dataAll=[];
            foreach ($data as $title => $dat) {
                $dataAll[$dat['meta_key']]  = $dat['meta_value'];
                $dataAll[$dat['meta_key'] . '_is_default'] = $dat['is_default'];
                $dataAll[$dat['meta_key'] . '_default_value'] = $dat['default_value'];
            }

            $object->addData(['currentStoreId'=>$currentStoreId]);

            if (!empty($data)) {
                $object->addData($dataAll);
            }
        }

        return $object;
    }

    /**
     * @param $currentRecipeId
     * @param $urlKey
     * @return bool
     */
    private function isUrlKeyExistInRecipes($currentRecipeId, $urlKey)
    {
        $connection = $this->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $selects = $connection->select()
            ->from(["main_table"=>$this->getTable("ms_recipe")])
            ->reset(\Zend_Db_Select::COLUMNS)
            ->where("url_key = \"" . $urlKey . "\" and recipe_id != \"" . $currentRecipeId . "\"")
            ->columns(['url_key','recipe_id']);
        $data=$connection->fetchAll($selects);

        return (count($data) > 0) ? false : true;
    }
}
