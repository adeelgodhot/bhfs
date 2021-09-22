<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model;

use Amasty\Base\Model\Serializer;
use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Plugin\App\Config\ScopeCodeResolver;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Store\Model\App\Emulation;

/**
 * @SuppressWarnings(PHPMD)
 */
class Rule extends \Magento\CatalogRule\Model\Rule
{
    const BATCH_SIZE = 1000;
    const PRODUCT = 'product';
    const STORE_ID = 'store_id';
    const LABEL = 'label';

    /**
     * @var Serializer
     */
    private $amastySerializer;

    /**
     * @var Emulation
     */
    private $storeEmulation;

    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    protected function _construct()
    {
        $this->amastySerializer = $this->getData('amastySerializer');
        $this->storeEmulation = $this->getData('storeEmulation');
        $this->scopeCodeResolver = $this->getData('scopeCodeResolver');
        if (!$this->amastySerializer) {
            $this->amastySerializer = $this->serializer;
        }
        parent::_construct();
        $this->_init(\Amasty\Label\Model\ResourceModel\Label::class);
        $this->setIdFieldName('entity_id');
    }

    /**
     * @param array $ids
     */
    public function setProductFilter($ids)
    {
        $this->_productsFilter = $ids;
    }

    /**
     * create new function because it should be compatible with parent class
     * @param LabelInterface $label
     *
     * @return array|null
     */
    public function getMatchingProductIdsByLabel(?LabelInterface $label = null)
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);
            $this->scopeCodeResolver->setNeedClean(true);
            foreach (explode(',', $this->getStores()) as $storeId) {
                $this->storeEmulation->startEnvironmentEmulation($storeId, true);
                /** @var $productCollection ProductCollection */
                $productCollection = $this->_productCollectionFactory->create()
                    ->setStoreId($storeId);

                if ($this->_productsFilter) {
                    $productCollection->addIdFilter($this->_productsFilter);
                }

                $this->getConditions()->collectValidatedAttributes($productCollection);

                /** @var Product $product **/
                foreach ($this->getProducts($productCollection) as $product) {
                    $this->callbackValidateProduct([
                        self::PRODUCT => $product,
                        self::STORE_ID => $storeId,
                        self::LABEL => $label
                    ]);
                }

                $this->storeEmulation->stopEnvironmentEmulation();
            }
        }

        return $this->_productIds;
    }

    private function getProducts(ProductCollection $collection): iterable
    {
        $collection->setPageSize(self::BATCH_SIZE);
        $lastPageNumber = $collection->getLastPageNumber();

        for ($pageNumber = 1; $pageNumber <= $lastPageNumber; ++$pageNumber) {
            $batchCollection = clone $collection;

            yield from $batchCollection->setCurPage($pageNumber);
        }
    }

    /**
     * @param array $args
     */
    public function callbackValidateProduct($args)
    {
        $product = $args[self::PRODUCT];
        $storeId = (int) $args[self::STORE_ID];
        $product->setStoreId($storeId);
        $result = $this->getConditions()->validate($product);

        if ($result) {
            $this->_productIds[$product->getId()][$storeId] = true;
        }
    }

    /**
     * fix fatal error after migration from 2.1 to 2.2 magento
     * Retrieve rule combine conditions model
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();

            if (!empty($conditions)) {
                $conditions = $this->unserializeConditions($conditions);

                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }

    /**
     * @param $conditions
     *
     * @return array|bool|float|int|mixed|string|null
     */
    public function unserializeConditions($conditions)
    {
        $resultCondition = $this->amastySerializer->unserialize($conditions);

        if ($resultCondition === false) {
            $resultCondition = $this->serializer->unserialize($conditions);
        }

        return $resultCondition;
    }
}
