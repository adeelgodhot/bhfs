<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\ResourceModel\Label;

use Amasty\Label\Api\Data\LabelFrontendSettingsInterface;
use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\Label;
use Amasty\Label\Model\ResourceModel\Label as LabelResource;
use Amasty\Label\Model\Source\Status;
use Amasty\Label\Setup\Uninstall;
use Magento\Framework\Api\ExtensionAttribute\JoinDataInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends AbstractCollection
{
    const MODE_LIST = 1;
    const MODE_PDP = 2;
    const STORE_ID_FIELD = 'store_id';
    const CUSTOMER_GROUP_ID = 'customer_group_id';

    /**
     * @var int
     */
    private $currentMode = self::MODE_PDP;

    /**
     * @var string
     */
    protected $_eventPrefix = 'amasty_label_entity_collection';

    /**
     * @var int[]
     */
    private $stores = [];

    /**
     * @var int[]
     */
    private $customerGroups = [];

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    protected function _construct(): void
    {
        $this->_init(Label::class, LabelResource::class);
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
        $this->addFilterToMap(LabelInterface::LABEL_ID, 'main_table.' . LabelInterface::LABEL_ID);
    }

    public function addActiveFilter(): void
    {
        $this->addFieldToFilter(LabelInterface::STATUS, Status::ACTIVE);
    }

    public function addFieldToFilter($field, $condition = null)
    {
        $field = is_string($field) && strpos($field, '.') === false ? "main_table.{$field}" : $field;
        parent::addFieldToFilter($field, $condition); // prevent ambiguous sql errors

        return $this;
    }

    /**
     * @param int|array $storeFilter
     */
    public function addStoreFilter($storeFilter): void
    {
        $this->stores = array_merge($this->stores, (array)$storeFilter);
    }

    /**
     * @param int|array $customerFilter
     */
    public function addCustomerGroupFilter($customerFilter): void
    {
        $this->customerGroups = array_merge($this->customerGroups, (array)$customerFilter);
    }

    public function joinExtensionAttribute(
        JoinDataInterface $join,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        parent::joinExtensionAttribute($join, $extensionAttributesJoinProcessor);

        if ($join->getReferenceTable() === Uninstall::AMASTY_LABEL_CATALOG_PARTS_TABLE) {
            $this->addModeFilter($join->getReferenceTableAlias());
        }

        return $this;
    }

    private function addModeFilter(string $tableAlias): void
    {
        $this->addFieldToFilter(
            sprintf('%s.%s', $tableAlias, LabelFrontendSettingsInterface::TYPE),
            $this->currentMode
        );
    }

    protected function _renderFiltersBefore(): void
    {
        $this->extensionAttributesJoinProcessor->process($this);
        parent::_renderFiltersBefore();
    }

    protected function _renderFilters()
    {
        if (!$this->_isFiltersRendered) {
            $this->filterByRelatedEntity(
                Uninstall::AMASTY_LABEL_STORE_TABLE,
                $this->stores,
                self::STORE_ID_FIELD
            );
            $this->filterByRelatedEntity(
                Uninstall::AMASTY_LABEL_CUSTOMER_GROUP_TABLE,
                $this->customerGroups,
                self::CUSTOMER_GROUP_ID
            );
        }

        return parent::_renderFilters();
    }

    /**
     * Joins related table and adds filter by ids of related entity
     *
     * @param string $tableName
     * @param int[] $values
     * @param string $conditionColumn
     */
    private function filterByRelatedEntity(string $tableName, array $values, string $conditionColumn): void
    {
        if (count($values) > 0) {
            $select = $this->getSelect();
            $tableName = $this->getTable($tableName);
            $select->join(
                $tableName,
                sprintf('main_table.%1$s = %2$s.%1$s', LabelInterface::LABEL_ID, $tableName),
                []
            );
            $values = array_map('intval', $values);
            $isSingleValue = count($values) === 1;
            $condition = $this->_getConditionSql(
                $conditionColumn,
                [$isSingleValue ? 'eq' : 'in' => $isSingleValue ? reset($values) : $values]
            );
            $select->where($condition);

            if ($isSingleValue) {
                $select->group($this->_getMappedField(LabelInterface::LABEL_ID));
            }
        }
    }

    public function addIsNewFilterApplied(): void
    {
        $isNewCondition = $this->_getConditionSql(
            LabelInterface::CONDITION_SERIALIZED,
            ['like' => '%Condition\\\\\\\\IsNew%']
        );
        $this->getSelect()->where($isNewCondition);
    }

    public function getItemById($idValue): ?LabelInterface
    {
        $this->load();
        /** @var ?LabelInterface $label **/
        $label = $this->_items[$idValue] ?? null;

        return $label;
    }

    /**
     * Sets a value indicating what type of frontend settings to load.
     * Possible values 1 (product list) 2 (product page)
     *
     * @param int $mode
     */
    public function setMode(int $mode): void
    {
        $mode = $mode === self::MODE_LIST ? self::MODE_LIST : self::MODE_PDP;
        $this->currentMode = $mode;
    }
}
