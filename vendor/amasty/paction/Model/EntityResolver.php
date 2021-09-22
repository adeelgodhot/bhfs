<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model;

use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class EntityResolver
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resource
    ) {
        $this->metadataPool = $metadataPool;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    public function getEntityLinkField(string $entityType): string
    {
        return $this->getEntityMetadata($entityType)->getLinkField();
    }

    public function getEntityLinkIds(string $entityType, array $ids): array
    {
        if ($this->getEntityLinkField($entityType) === 'entity_id') {
            return $ids;
        }

        $tableName = $this->getEntityMetadata($entityType)->getEntityTable();
        $select = $this->connection->select()
            ->from($tableName, ['row_id'])
            ->where('entity_id IN (?)', $ids);

        return $this->connection->fetchCol($select);
    }

    private function getEntityMetadata(string $entityType): EntityMetadataInterface
    {
        return $this->metadataPool->getMetadata($entityType);
    }
}
