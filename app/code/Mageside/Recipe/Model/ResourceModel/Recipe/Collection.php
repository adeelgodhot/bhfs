<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\Recipe\Model\ResourceModel\Recipe;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\App\RequestInterface;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_idFieldName = 'recipe_id';

    /**
     * @var Filter\CollectionFactory
     */
    protected $_filterCollectionFactory;

    /**
     * @var \Mageside\Recipe\Model\ResourceModel\RecipeProduct\CollectionFactory
     */
    protected $_recipeProductCollectionF;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(
            \Mageside\Recipe\Model\Recipe::class,
            \Mageside\Recipe\Model\ResourceModel\Recipe::class
        );
    }

    /**
     * Collection constructor.
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Filter\CollectionFactory $filterCollectionFactory
     * @param \Mageside\Recipe\Model\ResourceModel\RecipeProduct\CollectionFactory $recipeProductCollectionF
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\CollectionFactory $filterCollectionFactory,
        \Mageside\Recipe\Model\ResourceModel\RecipeProduct\CollectionFactory $recipeProductCollectionF,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_filterCollectionFactory = $filterCollectionFactory;
        $this->_recipeProductCollectionF = $recipeProductCollectionF;
        $this->_storeManager = $storeManager;
        $this->_request = $request;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    public function applySelectedFilters($filters)
    {
        if (isset($filters['product_id'])) {
            $inCond = $this->getConnection()
                ->prepareSqlCondition(
                    'product.product_id',
                    ['eq' => $filters['product_id']]
                );
            $select = $this->getSelect();
            if ($filters) {
                $select
                    ->join(
                        ['product' => $this->getTable('ms_recipe_product')],
                        'main_table.recipe_id = product.recipe_id AND ' . $inCond,
                        []
                    );
            }
        } else {
            $filterCollection = $this->_filterCollectionFactory->create();
            $select = $this->getSelect();
            foreach ($filterCollection->getItems() as $filter) {
                if (isset($filters[$filter->getCode()])) {
                    $aliasDataTable = 'filter_' . $filter->getCode();
                    $aliasOptionsTable = 'filter_option_' . $filter->getCode();
                    $select
                        ->join(
                            [$aliasDataTable => $this->getTable('ms_recipe_filter_data')],
                            'main_table.recipe_id = ' . $aliasDataTable . '.recipe_id AND '
                            . $aliasDataTable . '.filter_id = \'' . $filter->getId() . '\'',
                            []
                        )
                        ->join(
                            [$aliasOptionsTable => $this->getTable('ms_recipe_filter_options')],
                            $aliasDataTable . '.filter_options_id = ' . $aliasOptionsTable . '.id AND '
                            . $aliasOptionsTable . '.slug = \'' . $filters[$filter->getCode()] . '\'',
                            []
                        );
                }
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad()
    {
        $filterOptions = $this->loadAdditionalOptions();
        $recipeStores = $this->loadStoresData();

        foreach ($this->getItems() as $recipe) {
            if (!empty($filterOptions)) {
                if (array_key_exists($recipe->getId(), $filterOptions)) {
                    $recipe->addData(['options' => $filterOptions[$recipe->getId()]]);
                }
            }
            if (!empty($recipeStores)) {
                if (array_key_exists($recipe->getId(), $recipeStores)) {
                    $recipe->addData(['store_view' => $recipeStores[$recipe->getId()]]);
                }
            }
            if ($servingNumber = $recipe->getServingsNumber()) {
                $numbers = explode('-', $servingNumber);
                $recipe->setServingsNumberFrom(trim($numbers[0]));
                $recipe->setServingsNumberTo(trim($numbers[1]));
            }
            if ($ingredients = $recipe->getIngredients()) {
                $recipe->setIngredients(json_decode($ingredients, true));
            }
            if ($method = $recipe->getMethod()) {
                $recipe->setMethod(json_decode($method, true));
            }
        }

        parent::_afterLoad();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function loadAdditionalOptions()
    {
        $options = [];
        $ids = $this->getAllIds();
        if (!empty($ids)) {
            $connection = $this->getConnection();
            $currentStoreId = $this->_storeManager->getStore()->getId();
            $joinFields = [
                'label' => 'varchar',
            ];

            foreach ($joinFields as $fieldName => $backendType) {
                $tableName = $this->getTable('ms_recipe_filter_' . $backendType);

                $select = $connection->select()
                    ->from(['main_table' => $this->getTable('ms_recipe_filter_data')])
                    ->joinLeft(
                        ['filter' => $this->getTable('ms_recipe_filter')],
                        'main_table.filter_id = filter.id',
                        ['code']
                    )
                    ->joinLeft(
                        [
                            'filter_options' => $this->getTable('ms_recipe_filter_options')],
                        'main_table.filter_options_id = filter_options.id',
                        [
                            'option_id' => 'id',
                            'image' => 'option_image'
                        ]
                    )
                    ->joinLeft(
                        ["at_{$fieldName}" => $tableName],
                        "main_table.filter_options_id = at_{$fieldName}.filter_id 
                        AND at_{$fieldName}.meta_key=\"{$fieldName}\" 
                        AND at_{$fieldName}.store_id = 0",
                        null
                    )
                    ->joinLeft(
                        ["at_{$fieldName}_store" => $tableName],
                        "main_table.filter_options_id = at_{$fieldName}_store.filter_id 
                        AND at_{$fieldName}_store.meta_key=\"{$fieldName}\" 
                        AND at_{$fieldName}_store.store_id = $currentStoreId",
                        null
                    )
                    ->where('main_table.recipe_id in (?)', $ids)
                    ->columns([
                        "option_$fieldName" =>
                            new \Zend_Db_Expr(
                                "IFNULL(at_{$fieldName}_store.meta_value, at_{$fieldName}.meta_value)"
                            )
                    ]);
            }

            $filtersData = $connection->fetchAll($select);

            if (!empty($filtersData)) {
                foreach ($filtersData as $record) {
                    $options[$record['recipe_id']][$record['code']][$record['option_id']] = [
                        'option_id' => $record['option_id'],
                        'option_label' => $record['option_label'],
                        'option_image' => $record['image']
                    ];
                }
            }
        }

        return $options;
    }

    /**
     * @return array
     */
    private function loadStoresData()
    {
        $stores = [];
        $ids = $this->getAllIds();
        if (!empty($ids)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['main_table' => $this->getTable('ms_recipe_store')])
                ->where('main_table.recipe_id in (?)', $ids);

            $storesData = $connection->fetchAll($select);

            if (!empty($storesData)) {
                foreach ($storesData as $record) {
                    $stores[$record['recipe_id']][] = $record['store_id'];
                }
            }
        }

        return $stores;
    }

    /**
     * @param $keyword
     * @return $this
     */
    public function applySearchKeywordFilter($keyword)
    {
        $attributesPriorities = [
            'IFNULL(at_title_store.meta_value, at_title.meta_value)' => 100,
            'IFNULL(at_short_description_store.meta_value, at_short_description.meta_value)' => 50,
            'IFNULL(at_ingredients_store.meta_value, at_ingredients.meta_value)' => 25,
            'IFNULL(at_method_store.meta_value, at_method.meta_value)' => 10
        ];

        $keywordWords = explode(' ', $keyword);

        $weightColumnExpression = [];
        $connection = $this->getConnection();
        foreach ($attributesPriorities as $attribute => $priority) {
            $conditions = [];
            foreach ($keywordWords as $keywordWord) {
                if ($keywordWord = trim($keywordWord)) {
                    $conditions[] = $connection->quoteInto(
                        "(LOWER({$attribute}) LIKE LOWER(?))",
                        ['like' => '%' . $keywordWord . '%']
                    );
                }
            }
            $condition = implode(' AND ', $conditions);
            $weightColumnExpression[] = $connection->getCheckSql($condition, $priority, 0);
        }
        $weightColumnExpression = '(' . implode(') + (', $weightColumnExpression) . ')';
        $this->getSelect()->where(new \Zend_Db_Expr($weightColumnExpression));

        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addStoreFilter()
    {
        $stores = [0];

        $store = $this->_storeManager->getStore();
        if ($store instanceof \Magento\Store\Model\Store) {
            $stores[] = $store->getId();
        }

        $this->getSelect()
            ->join(
                ['store_table' => $this->getTable('ms_recipe_store')],
                'main_table.recipe_id = store_table.recipe_id',
                []
            )
            ->where('store_table.store_id IN (?)', $stores);

        return $this;
    }

    /**
     * @return $this
     */
    public function addIsEnableFilter()
    {
        $this->addFieldToFilter('status', 1);

        return $this;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _beforeLoad()
    {
        $this->joinRecipeData();
        return parent::_beforeLoad();
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function joinRecipeData()
    {
        $currentStoreId = $this->_request->getParam('store');
        if (!$currentStoreId) {
            $currentStoreId = 0;
        }
        $joinFields = [
            'title' => 'varchar',
            'ingredients' => 'text',
            'short_description' => 'text',
            'method' => 'text',
        ];

        foreach ($joinFields as $fieldName => $backendType) {
            $tableName = $this->getTable('ms_recipe_' . $backendType);

            $this->getSelect()
                ->joinLeft(
                    ["at_{$fieldName}" => $tableName],
                    "main_table.recipe_id = at_{$fieldName}.recipe_id
                     AND at_{$fieldName}.meta_key=\"{$fieldName}\"
                     AND at_{$fieldName}.store_id = 0",
                    null
                )
                ->joinLeft(
                    ["at_{$fieldName}_store" => $tableName],
                    "main_table.recipe_id = at_{$fieldName}_store.recipe_id
                    AND at_{$fieldName}_store.meta_key=\"{$fieldName}\"
                    AND at_{$fieldName}_store.store_id = $currentStoreId",
                    null
                )
                ->columns([
                    $fieldName =>
                        new \Zend_Db_Expr(
                            "IFNULL(at_{$fieldName}_store.meta_value, at_{$fieldName}.meta_value)"
                        )
                ]);
        }

        return $this;
    }

    public function getCollectionById($ids)
    {
        $inCond = [];
        if (isset($ids)) {
            $inCond = $this->getSelect()
            ->where('main_table.recipe_id IN (?)', $ids);
        }

            return $inCond;
    }
}
