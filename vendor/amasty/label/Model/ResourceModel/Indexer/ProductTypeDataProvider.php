<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\ResourceModel\Indexer;

use Amasty\Label\Model\Indexer\IndexBuilder;
use Amasty\Label\Setup\Uninstall;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTypeDataProvider
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var RowIdToEntityIdConverter
     */
    private $rowIdToEntityIdConverter;

    public function __construct(
        ResourceConnection $resourceConnection,
        MetadataPool $metadataPool,
        ProductMetadataInterface $productMetadata,
        RowIdToEntityIdConverter $rowIdToEntityIdConverter,
        int $batchSize = 1000
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->batchSize = $batchSize > 0 ? $batchSize : 1;
        $this->metadataPool = $metadataPool;
        $this->productMetadata = $productMetadata;
        $this->rowIdToEntityIdConverter = $rowIdToEntityIdConverter;
    }

    /**
     * @param int[]|null $productIds
     * @return iterable
     */
    public function getProductChildrenIds(?array $productIds = null): iterable
    {
        yield from $this->iterateOverSelect($this->getConfigurableSelect($productIds));
        yield from $this->iterateOverSelect($this->getGroupedSelect($productIds));
    }

    public function iterateOverSelect(Select $select): iterable
    {
        $rowsCount = $this->getRowsCount($select);
        $pageAmount = (int) ceil($rowsCount / $this->batchSize);

        for ($currentPage = 1; $currentPage <= $pageAmount; $currentPage++) {
            $select->limitPage($currentPage, $this->batchSize);
            $childIds = $this->resourceConnection->getConnection()->fetchPairs($select);
            $parentIds = array_keys($childIds);
            $parentIds = $this->isEnterpriseEdition()
                ? $this->rowIdToEntityIdConverter->convertList($parentIds)
                : array_combine($parentIds, $parentIds);

            foreach ($childIds as $productId => $childrenIds) {
                $childrenIds = $childrenIds === '' ? [] : explode(',', $childrenIds);
                $childrenIds = array_map('intval', $childrenIds);

                yield $parentIds[$productId] ?? $productId => $childrenIds;
            }
        }
    }

    private function getRowsCount(Select $select): int
    {
        $countSelect = clone $select;
        $countSelect->reset(Select::ORDER);
        $countSelect->reset(Select::LIMIT_COUNT);
        $countSelect->reset(Select::LIMIT_OFFSET);
        $countSelect = $this->resourceConnection->getConnection()->select()->from($countSelect, []);
        $countSelect->columns(['count' => new \Zend_Db_Expr('COUNT(*)')]);

        return (int) $this->resourceConnection->getConnection()->fetchOne($countSelect);
    }

    private function getConfigurableSelect(?array $productIds = null): Select
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            ['l' => $this->resourceConnection->getTableName('catalog_product_super_link')],
            ['parent_id' => 'parent_id', 'child_ids' => new \Zend_Db_Expr('group_concat(distinct product_id)')]
        )->join(
            ['p' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'p.' . $this->getLinkField() . ' = l.parent_id',
            []
        )->join(
            ['e' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'e.entity_id = l.product_id AND e.required_options = 0',
            []
        )->where(
            $connection->prepareSqlCondition('p.type_id', ['eq' => Configurable::TYPE_CODE])
        )->having(
            $connection->prepareSqlCondition(
                new \Zend_Db_Expr('group_concat(distinct product_id)'),
                ['neq' => '']
            )
        )->group('parent_id');

        if ($productIds !== null) {
            $select->where($connection->prepareSqlCondition('product_id', ['in' => $productIds]));
        } else {
            $select->where(
                $connection->prepareSqlCondition('product_id', ['in' => $this->getIndexedProductIds()])
            );
        }

        return $select;
    }

    private function getLinkField(): string
    {
        return $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
    }

    private function getGroupedSelect(?array $productIds = null): Select
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->from(
            ['l' => $this->resourceConnection->getTableName('catalog_product_link')],
            ['linked_product_id']
        )->join(
            ['cpe' => $this->resourceConnection->getTableName('catalog_product_entity')],
            sprintf(
                'cpe.%s = l.product_id',
                $this->getLinkField()
            )
        )->join(
            ['e' => $this->resourceConnection->getTableName('catalog_product_entity')],
            'e.entity_id = l.linked_product_id AND e.required_options = 0',
            []
        )->where(
            $connection->prepareSqlCondition('cpe.type_id', ['eq' => Grouped::TYPE_CODE])
        )->where(
            $connection->prepareSqlCondition('link_type_id', ['eq' => Link::LINK_TYPE_GROUPED])
        )->having(
            $connection->prepareSqlCondition(
                new \Zend_Db_Expr('group_concat(distinct l.linked_product_id)'),
                ['neq' => '']
            )
        )->reset(
            Select::COLUMNS
        )->columns([
            'parent_id' => 'l.product_id',
            'child_ids' => new \Zend_Db_Expr('group_concat(distinct l.linked_product_id)')
        ])->group('l.product_id');

        if ($productIds !== null) {
            $select->where($connection->prepareSqlCondition('l.linked_product_id', ['in' => $productIds]));
        } else {
            $select->where(
                $connection->prepareSqlCondition('l.linked_product_id', ['in' => $this->getIndexedProductIds()])
            );
        }

        return $select;
    }

    private function getIndexedProductIds(): Select
    {
        $select = $this->resourceConnection->getConnection()->select();
        $select->from(
            ['ali' => $this->resourceConnection->getTableName(Uninstall::AMASTY_LABEL_INDEX_TABLE)],
            [IndexBuilder::PRODUCT_ID]
        );
        $select->distinct();

        return $select;
    }

    public function isEnterpriseEdition(): bool
    {
        return $this->productMetadata->getEdition() !== ProductMetadata::EDITION_NAME;
    }
}
