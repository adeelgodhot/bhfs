<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model\Indexer;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\Label;
use Amasty\Label\Model\Label\GetMatchedProductIdsInterface;
use Amasty\Label\Model\ResourceModel\Indexer\ProductTypeDataProvider;
use Amasty\Label\Model\ResourceModel\Label\Collection as LabelCollection;
use Amasty\Label\Model\ResourceModel\Label\CollectionFactory;
use Amasty\Label\Model\ResourceModel\Label\GetRelatedEntitiesIds as GetStoreIdsByLabelId;
use Amasty\Label\Setup\Uninstall;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class IndexBuilder
{
    const PRODUCT_ID = 'product_id';
    const STORE_ID = 'store_id';

    /**
     * @var LabelCollection|null
     */
    private $fullLabelCollection = null;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var int
     */
    private $batchCount;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var int
     */
    private $batchCacheCount;

    /**
     * @var GetStoreIdsByLabelId
     */
    private $getStoreIdsByLabelId;

    /**
     * @var GetMatchedProductIdsInterface
     */
    private $getMatchedProductIds;

    /**
     * @var ProductTypeDataProvider
     */
    private $productTypeDataProvider;

    public function __construct(
        ResourceConnection $resource,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        ProductRepository $productRepository,
        ProductCollectionFactory $productCollectionFactory,
        CacheContext $cacheContext,
        ManagerInterface $eventManager,
        GetStoreIdsByLabelId $getStoreIdsByLabelId,
        GetMatchedProductIdsInterface $getMatchedProductIds,
        ProductTypeDataProvider $productTypeDataProvider,
        $batchCount = 1000,
        $batchCacheCount = 100
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->batchCount = $batchCount;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->cacheContext = $cacheContext;
        $this->eventManager = $eventManager;
        $this->batchCacheCount = $batchCacheCount;
        $this->getStoreIdsByLabelId = $getStoreIdsByLabelId;
        $this->getMatchedProductIds = $getMatchedProductIds;
        $this->productTypeDataProvider = $productTypeDataProvider;
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     * @return void
     * @throws LocalizedException
     * @api
     */
    public function reindexByProductIds(array $ids)
    {
        $connection = $this->resource->getConnection();
        $connection->beginTransaction();
        try {
            $this->cleanByProductIds($ids);
            $this->doReindexByProductIds($ids);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->critical($e);
            throw new LocalizedException(
                __("Amasty label indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * Reindex by label ids
     *
     * @param array $ids
     * @return void
     * @throws LocalizedException
     * @api
     */
    public function reindexByLabelIds($ids)
    {
        $connection = $this->resource->getConnection();
        $connection->beginTransaction();

        try {
            $this->cleanByLabelIds($ids);
            $this->doReindexByLabelIds($ids);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->critical($e);
            throw new LocalizedException(
                __("Amasty label indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * @param $id
     * @throws LocalizedException
     */
    public function reindexByProductId($id)
    {
        $this->reindexByProductIds((array) $id);
    }

    /**
     * @param $id
     * @throws LocalizedException
     */
    public function reindexByLabelId($id)
    {
        $this->reindexByLabelIds([$id]);
    }

    /**
     * @param int[] $productIds
     */
    private function cleanByProductIds(array $productIds)
    {
        if (!empty($productIds)) {
            $connection = $this->resource->getConnection();
            $connection->delete(
                $this->getMainTable(),
                $connection->prepareSqlCondition(self::PRODUCT_ID, ['in' => $productIds])
            );
        }
    }

    private function getMainTable(): string
    {
        return $this->resource->getTableName(Uninstall::AMASTY_LABEL_INDEX_TABLE);
    }

    /**
     * @param int[] $labelIds
     */
    private function cleanByLabelIds(array $labelIds)
    {
        if (!empty($labelIds)) {
            $connection = $this->resource->getConnection();
            $connection->delete(
                $this->getMainTable(),
                $connection->prepareSqlCondition(LabelInterface::LABEL_ID, ['in' => $labelIds])
            );
        }
    }

    /**
     * @param array $ids
     * @return $this
     */
    private function doReindexByProductIds($ids)
    {
        $labels = $this->getFullLabelCollection()->getItems();

        /** @var Label $label **/
        foreach ($labels as $label) {
            $this->reindexByLabelAndProductIds($label, $ids);
        }

        $this->renderChildrenLabelsOnParent($labels, $ids);

        return $this;
    }

    /**
     * @param array $ids
     * @return $this
     */
    private function doReindexByLabelIds($ids)
    {
        $labels = $this->getLabelCollection($ids)->getItems();

        /** @var Label $label **/
        foreach ($labels as $label) {
            $this->reindexByLabelAndProductIds($label);
            $this->cacheContext->registerEntities(Label::CACHE_TAG, [$label->getId()]);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        }

        $this->renderChildrenLabelsOnParent($labels);

        return $this;
    }

    /**
     * @param Label $label
     * @param $ids
     * @return $this
     */
    private function reindexByLabelAndProductIds(Label $label, $ids = null)
    {
        $matchedProductIds = $this->getMatchedProductIds->execute($label, $ids);
        $this->insertMatchedData($label, $matchedProductIds);

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @param LabelInterface $label
     * @param array $matchedProductIds
     */
    private function insertMatchedData(
        LabelInterface $label,
        array $matchedProductIds
    ): void {
        $rows = [];
        $productIds = [];
        $count = 0;
        $labelStoreIds = $this->getStoreIdsByLabelId->execute($label->getLabelId());

        if (!empty($labelStoreIds) && !empty($matchedProductIds)) {
            /** @var int[] $matchedStores **/
            foreach ($matchedProductIds as $productId => $matchedStores) {
                $stores = array_intersect(array_keys($matchedStores), $labelStoreIds);

                if ($stores) {
                    foreach ($stores as $storeId) {
                        $rows[] = [
                            self::PRODUCT_ID => (int) $productId,
                            LabelInterface::LABEL_ID => $label->getLabelId(),
                            self::STORE_ID => $storeId
                        ];
                        $count++;
                    }
                    $productIds[] = (int) $productId;

                    if ($count >= $this->batchCount) {
                        $this->insertData($rows);
                        $rows = [];
                        $count = 0;
                    }

                    if (count($productIds) > $this->batchCacheCount) {
                        $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
                        $this->eventManager->dispatch(
                            'clean_cache_by_tags',
                            ['object' => $this->cacheContext]
                        );

                        $productIds = [];
                    }
                }
            }
        }

        if (!empty($rows)) {
            $this->insertData($rows);
        }

        if (!empty($productIds)) {
            $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        }
    }

    private function insertData(array $data): void
    {
        $this->resource->getConnection()->insertOnDuplicate($this->getMainTable(), $data);
    }

    /**
     * @api
     *
     * Full reindex
     *
     * @return void
     * @throws LocalizedException
     */
    public function reindexFull()
    {
        $connection = $this->resource->getConnection();
        $connection->truncateTable($this->getMainTable());
        $connection->beginTransaction();

        try {
            $this->doReindexFull();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->critical($e->getMessage());
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @param null $labelIds
     * @return mixed
     */
    private function getLabelCollection($labelIds = null): LabelCollection
    {
        $collection = $this->collectionFactory->create();
        $collection->addActiveFilter();

        if ($labelIds) {
            $collection->addFieldToFilter(LabelInterface::LABEL_ID, ['in' => $labelIds]);
        }

        return $collection;
    }

    private function getFullLabelCollection(): LabelCollection
    {
        if ($this->fullLabelCollection === null) {
            $this->fullLabelCollection = $this->collectionFactory->create();
            $this->fullLabelCollection->addActiveFilter();
        }

        return $this->fullLabelCollection;
    }

    /**
     * @return $this
     */
    private function doReindexFull()
    {
        $labels = $this->getFullLabelCollection()->getItems();

        /** @var Label $label **/
        foreach ($labels as $label) {
            $this->reindexByLabelAndProductIds($label);
        }

        $this->renderChildrenLabelsOnParent($labels);

        return $this;
    }

    private function renderChildrenLabelsOnParent(array $labels, ?array $productIds = null): void
    {
        $labelIds = array_reduce($labels, function (array $carry, LabelInterface $label): array {
            if ($label->getUseForParent()) {
                $carry[] = $label->getLabelId();
            }

            return $carry;
        }, []);

        if (!empty($labelIds)) {
            /** @var array $stores * */
            foreach ($this->productTypeDataProvider->getProductChildrenIds($productIds) as $productId => $childrenIds) {
                $this->addChildLabelsToParent($labelIds, $productId, $childrenIds);
            }
        }
    }

    private function addChildLabelsToParent(array $labelIds, int $productId, array $childIds): void
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select();
        $select->from($this->getMainTable());
        $select->where($connection->prepareSqlCondition(LabelInterface::LABEL_ID, ['in' => $labelIds]));
        $select->where($connection->prepareSqlCondition(self::PRODUCT_ID, ['in' => $childIds]));
        $select->reset(Select::COLUMNS);
        $select->columns([
            LabelInterface::LABEL_ID,
            self::PRODUCT_ID => new \Zend_Db_Expr($productId),
            self::STORE_ID
        ]);
        $query = $connection->insertFromSelect(
            $select,
            $this->getMainTable(),
            [LabelInterface::LABEL_ID, self::PRODUCT_ID, self::STORE_ID],
            AdapterInterface::INSERT_ON_DUPLICATE
        );
        $connection->query($query);
    }
}
