<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\Recipe\Model\Recipe;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Options\CollectionFactory;

class Filter extends AbstractModel
{
    /**
     * @var \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Options\CollectionFactory
     */
    protected $optionsCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * Filter constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Options\CollectionFactory $optionsCollectionFactory
     * @param RequestInterface $request
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $optionsCollectionFactory,
        RequestInterface $request,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->optionsCollectionFactory = $optionsCollectionFactory;
        $this->_request = $request;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(\Mageside\Recipe\Model\ResourceModel\Recipe\Filter::class);
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        $filterId = $this->getId();

        if ($this->hasData('options')) {
            return $this->getData('options');
        }

        $this->setData('options', $this->loadOptionsByFilterId($filterId)->getData());

        return $this->getData('options');
    }

    /**
     * @param $filterId
     * @return \Mageside\Recipe\Model\ResourceModel\Recipe\Filter\Options\Collection
     */
    public function loadOptionsByFilterId($filterId)
    {
        $options = $this->optionsCollectionFactory->create();
        $options->addFieldToFilter('filter_id', $filterId);

        return $options->load();
    }

    /**
     * @param $recipeData
     */
    public function saveRecipeFilterData($recipeData)
    {
        $currentFilterId = $this->getId();
        $currentStoreId = $this->getData('$store_id');
        $joinFields = [
            'type' => 'varchar'
        ];

        if ($currentFilterId) {
            $collection = $this->getCollection();
            $connection = $collection->getSelect()->getConnection();
            $useDefault = $this->_request->getParam('use_default');
            $filterDeleteIds = [];
            foreach ($joinFields as $fieldName => $backendType) {
                $tableName = $connection->getTableName('ms_recipe_filter_' . $backendType);
                $filterId = $connection->fetchOne(
                    $this->getCollection()->getConnection()->select()
                        ->from($tableName, ['id'])
                        ->where(
                            "meta_key = \"{$fieldName}\" AND 
                    store_id = " . $currentStoreId . " AND 
                    filter_id = " . $currentFilterId
                        )
                );
                if ($currentStoreId > 0 && !$useDefault['type']
                    || $currentStoreId == 0
                ) {
                    $prepareDate = [
                        "meta_key" => "{$fieldName}",
                        "store_id" => $currentStoreId,
                        "filter_id" => $currentFilterId,
                        "meta_value" => $recipeData["{$fieldName}"]
                    ];

                    if ($filterId) {
                        $select = $this->getCollection()->getConnection()->update(
                            $tableName,
                            $prepareDate,
                            "meta_key = \"{$fieldName}\" 
                            AND store_id = " . $currentStoreId . " 
                            AND filter_id = " . $currentFilterId
                        );
                    } else {
                        $select = $this->getCollection()->getConnection()->insert(
                            $tableName,
                            $prepareDate
                        );
                    }
                } elseif ($currentStoreId > 0 && $useDefault['type'] && $filterId) {
                    $filterDeleteIds[] = $filterId;
                }
                $collection->getConnection()->delete($tableName, ['id IN (?)' => $filterDeleteIds]);
            }
        }
    }
}
