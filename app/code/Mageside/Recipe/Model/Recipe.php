<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */

namespace Mageside\Recipe\Model;

class Recipe extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ResourceModel\RecipeProduct\Collection
     */
    protected $_recipeProductCollection;
    /**
     * @var
     */
    protected $_assignedProducts;
    /**
     * @var string
     */
    public $_mainTable;

    /**
     * Recipe constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\RecipeProduct\Collection $recipeProductCollection
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Mageside\Recipe\Model\ResourceModel\RecipeProduct\Collection $recipeProductCollection,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_recipeProductCollection = $recipeProductCollection;
        $this->_mainTable = 'ms_recipe';
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(\Mageside\Recipe\Model\ResourceModel\Recipe::class);
    }

    /**
     * @return array
     */
    public function getAssignedProductIds()
    {
        $productIds = [];
        $assignedProduct = $this->getAssignedProducts();
        foreach ($assignedProduct as $productId) {
            $productIds[] = $productId['product_id'];
        }

        return $productIds;
    }

    /**
     * @return array
     */
    public function getAssignedProducts()
    {
        if ($this->_assignedProducts === null) {
            $recipeId = $this->getId();
            $this->_assignedProducts = $this->_recipeProductCollection
                ->addFieldToFilter('recipe_id', ['eq' => $recipeId])
                ->getData();
        }

        return $this->_assignedProducts;
    }

    public function saveRecipeData($recipesData)
    {
        $currentRecipeId = $this->getId();
        $currentStoreId = $this->getStoreId();

        $joinFields = [
            'title' => 'varchar',
            'ingredients' => 'text',
            'short_description' => 'text',
            'method' => 'text',
        ];

        if ($currentRecipeId) {
            foreach ($joinFields as $fieldName => $backendType) {
                if (isset($recipesData['use_default'][$fieldName])) {
                    $this->updateRecipeData($backendType, $fieldName, $currentStoreId, $currentRecipeId, $recipesData['use_default'][$fieldName]);
                } elseif (isset($this->_data[$fieldName])) {
                    $this->updateRecipeData($backendType, $fieldName, $currentStoreId, $currentRecipeId, '0');
                }
            }
        }
    }

    /**
     * @param $backendType
     * @param $fieldName
     * @param $currentStoreId
     * @param $currentRecipeId
     */
    private function updateRecipeData($backendType, $fieldName, $currentStoreId, $currentRecipeId, $selected)
    {
        $collection = $this->getCollection();
        $connection = $collection->getSelect()->getConnection();
        $tableName = $collection->getTable('ms_recipe_' . $backendType);

        $prepareCurrentData = $this->prepareFieldData($fieldName, $currentStoreId, $currentRecipeId);

        if ($this->isDataExistByStore($tableName, $fieldName, $currentStoreId, $currentRecipeId)) {
            if ($selected == '0') {
                $connection->update(
                    $tableName,
                    $prepareCurrentData,
                    "meta_key = \"{$fieldName}\" AND 
                            store_id = " . $currentStoreId . " AND 
                            recipe_id = " . $currentRecipeId
                );
            } else {
                $connection->delete(
                    $tableName,
                    "meta_key = \"{$fieldName}\" AND 
                            store_id = " . $currentStoreId . " AND 
                            recipe_id = " . $currentRecipeId
                );
            }
        } else {
            if ($selected == '0') {
                $connection->insert($tableName, $prepareCurrentData);
            }
        }
    }

    /**
     * @param $tableName
     * @param $prepareDate
     * @param $fieldName
     * @param $currentStoreId
     * @param $currentRecipeId
     * @return bool
     */
    private function isDataExistByStore($tableName, $fieldName, $currentStoreId, $currentRecipeId)
    {
        $collection = $this->getCollection();
        $connection = $collection->getSelect()->getConnection();
        $select = $connection->select()
            ->from($tableName)
            ->where('meta_key = ?', $fieldName)
            ->where('store_id = ?', $currentStoreId)
            ->where('recipe_id = ?', $currentRecipeId);

        return $connection->fetchRow($select) ? true : false;
    }

    /**
     * @param $fieldName
     * @param $currentStoreId
     * @param $currentRecipeId
     * @return array
     */
    private function prepareFieldData($fieldName, $currentStoreId, $currentRecipeId)
    {
        if (($fieldName == 'ingredients') || ($fieldName == 'method')) {
            $prepareData = [
                "meta_key" => "{$fieldName}",
                "store_id" => $currentStoreId,
                "recipe_id" => $currentRecipeId,
                "meta_value" => $this->_data[$fieldName]
            ];
        } else {
            $prepareData = [
                "meta_key" => "{$fieldName}",
                "store_id" => $currentStoreId,
                "recipe_id" => $currentRecipeId,
                "meta_value" => $this->_data[$fieldName]
            ];
        }

        return $prepareData;
    }
}
