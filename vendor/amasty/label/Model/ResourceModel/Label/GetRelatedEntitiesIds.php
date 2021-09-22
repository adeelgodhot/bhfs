<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\ResourceModel\Label;

use Amasty\Label\Api\Data\LabelInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * The class is designed to get an array of IDs related to one-to-many entities.
 *
 * @api
 *
 * @since 2.0.0 stores and customer ids can be obtained to which the label belongs
 *
 * This class cannot be used directly
 *
 * @see \Amasty\Label\Model\ResourceModel\Label\GetLabelStoresIds
 * @see \Amasty\Label\Model\ResourceModel\Label\GetLabelCustomerGroupsIds
 */
class GetRelatedEntitiesIds
{
    const FETCH_ALL = 'fetch_all';
    const FETCH_ONE = 'fetch_one';

    /**
     * @var string
     */
    private $mainTable;

    /**
     * @var string
     */
    private $identifierField;

    /**
     * @var string
     */
    private $fetchStrategy;

    /**
     * @var string
     */
    private $aggregationField;

    /**
     * @var int[][]
     */
    private $cache = [];

    /**
     * @var bool
     */
    private $isCacheWarmed = false;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection,
        string $mainTable,
        string $identifierField,
        string $aggregationField,
        string $fetchStrategy = self::FETCH_ONE
    ) {
        $this->mainTable = $mainTable;
        $this->identifierField = $identifierField;
        $this->fetchStrategy = $fetchStrategy;
        $this->resourceConnection = $resourceConnection;
        $this->aggregationField = $aggregationField;
    }

    /**
     * @param int $labelId
     * @return int[]
     */
    public function execute(int $labelId): array
    {
        $this->warmupCache($labelId);

        return $this->cache[$labelId];
    }

    private function warmupCache(int $labelId): void
    {
        if ($this->fetchStrategy === self::FETCH_ALL && !$this->isCacheWarmed) {
            $this->cache = $this->fetchAll();
            $this->isCacheWarmed = true;
        }

        if (!isset($this->cache[$labelId])) {
            $this->cache[$labelId] = $this->fetchOne($labelId);
        }
    }

    /**
     * @return int[][]
     */
    private function fetchAll(): array
    {
        $rawIds = $this->resourceConnection->getConnection()->fetchPairs(
            $this->getFetchAllSelect()
        );

        return array_map([$this, 'parseAggregatedIds'], $rawIds);
    }

    /**
     * @param int $labelId
     * @return int[]
     */
    private function fetchOne(int $labelId): array
    {
        $select = $this->getFetchAllSelect();
        $select->where(sprintf('%s = ?', LabelInterface::LABEL_ID), $labelId);
        $rawResult = $this->resourceConnection->getConnection()->fetchPairs($select);
        $aggregatedIds = empty($rawResult) ? '' : reset($rawResult);

        return $this->parseAggregatedIds($aggregatedIds);
    }

    /**
     * @param string|null $commaSeparatedIds
     * @return int[]
     */
    private function parseAggregatedIds(?string $commaSeparatedIds): array
    {
        return $commaSeparatedIds !== null && $commaSeparatedIds !== ''
            ? array_map('intval', explode(',', $commaSeparatedIds)) : [];
    }

    private function getFetchAllSelect(): Select
    {
        $tableName = $this->resourceConnection->getTableName($this->mainTable);
        $select = $this->resourceConnection->getConnection()->select();
        $select->from($tableName);
        $select->reset(Select::COLUMNS);
        $select->columns([
            $this->identifierField,
            new \Zend_Db_Expr(sprintf('GROUP_CONCAT(%s)', $this->aggregationField))
        ]);
        $select->group($this->identifierField);

        return $select;
    }
}
