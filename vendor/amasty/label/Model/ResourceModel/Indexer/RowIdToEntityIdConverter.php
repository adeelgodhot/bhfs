<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\ResourceModel\Indexer;

use Magento\Framework\App\ResourceConnection;

class RowIdToEntityIdConverter
{
    const PRODUCT_TABLE = 'catalog_product_entity';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param int[] $rowIds
     * @return int[]
     */
    public function convertList(array $rowIds): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select();
        $select->from(
            $this->resourceConnection->getTableName(self::PRODUCT_TABLE),
            ['row_id', 'entity_id']
        );
        $select->where($connection->prepareSqlCondition('row_id', ['in' => $rowIds]));
        $select->distinct();
        $entityIds = (array) $connection->fetchPairs($select);

        return array_map('intval', $entityIds);
    }

    public function convertOne(int $rowId): int
    {
        $convertedIds = $this->convertList([$rowId]);

        return reset($convertedIds);
    }
}
