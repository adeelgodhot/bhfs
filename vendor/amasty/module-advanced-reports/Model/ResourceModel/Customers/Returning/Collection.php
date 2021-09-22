<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


declare(strict_types=1);

namespace Amasty\Reports\Model\ResourceModel\Customers\Returning;

use Amasty\Reports\Model\ResourceModel\Customers\Returning\Collection\SelectProvider;
use Amasty\Reports\Traits\Filters;

class Collection extends \Magento\Customer\Model\ResourceModel\Customer\Collection
{
    use Filters;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Amasty\Reports\Helper\Data
     */
    private $helper;

    /**
     * @var SelectProvider
     */
    private $selectProvider;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig,
        \Magento\Framework\App\RequestInterface $request, // TODO move it out of here
        \Amasty\Reports\Helper\Data $helper,
        \Amasty\Reports\Model\ResourceModel\Customers\Returning\Collection\SelectProvider $selectProvider,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        $modelName = self::CUSTOMER_MODEL_NAME
    ) {
        $this->_fieldsetConfig = $fieldsetConfig;
        $this->_modelName = $modelName;
        $this->request = $request;
        $this->helper = $helper;
        $this->selectProvider = $selectProvider;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $entitySnapshot,
            $fieldsetConfig,
            $connection,
            $modelName
        );
    }

    /**
     * @param $collection
     * @param string $tablePrefix
     */
    public function prepareCollection($collection, $tablePrefix = 'main_table')
    {
        $this->applyBaseFilters($collection, $tablePrefix);
        $this->applyToolbarFilters($collection, $tablePrefix);
    }

    /**
     * @param $collection
     * @param string $tablePrefix
     */
    public function applyBaseFilters($collection, $tablePrefix = 'main_table')
    {
        $collection->getSelect()->reset(\Zend_Db_Select::FROM);
        $collection->getSelect()->from(['main_table' => $this->getTable('sales_order')]);
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);

        list($periodSelect, $group) = $this->getIntervalSelectAndGroupBy($collection);

        $collection->getSelect()
            ->columns([
                'customerEmail' => 'customer_email',
                'count' => 'COUNT(entity_id)',
                'period' => $periodSelect,
                'created_date' => 'DATE(created_at)',
                'new_customers' => '(' . $this->selectProvider->getNewCustomersQuery() . ')',
                'entity_id' => 'CONCAT(entity_id,\'' . $this->createUniqueEntity() . '\')',
                'returning_customers' => $this->selectProvider->getReturningCustomersSelect(),
                'percent' => $this->selectProvider->getPercentSelect()
            ]);

        $collection->getSelect()->order("DATE(created_at) DESC");
        $collection->getSelect()->group($group);
    }

    /**
     * @param $collection
     * @param string $tablePrefix
     */
    public function applyToolbarFilters($collection, $tablePrefix = 'main_table')
    {
        $this->addFromFilter($collection, $tablePrefix);
        $this->addToFilter($collection, $tablePrefix);
        $this->addStoreFilter($collection);
    }

    /**
     * @param $collection
     * @return array
     */
    private function getIntervalSelectAndGroupBy($collection)
    {
        $filters = $this->getRequestParams();
        $interval = isset($filters['interval']) ? $filters['interval'] : 'day';
        $collection->getSelect()->reset(\Zend_Db_Select::GROUP);

        switch ($interval) {
            case 'year':
                $select = 'YEAR(DATE(created_at))';
                $group = 'YEAR(DATE(created_at))';
                break;
            case 'month':
                $select = 'CONCAT(YEAR(DATE(created_at)), \'-\', MONTH(DATE(created_at)))';
                $group = 'MONTH(DATE(created_at))';
                break;
            case 'week':
                $select = 'CONCAT(ADDDATE(DATE(DATE(created_at)), INTERVAL 1-DAYOFWEEK(DATE(created_at)) DAY), '
                    . '" - ", ADDDATE(DATE(DATE(created_at)), INTERVAL 7-DAYOFWEEK(DATE(created_at)) DAY))';
                $group = 'WEEK(DATE(created_at))';
                break;
            case 'day':
            default:
                $select = 'DATE(DATE(created_at))';
                $group = 'DATE(DATE(created_at))';
                break;
        }

        return [$select, $group];
    }

    /**
     * @param $collection
     * @param $tablePrefix
     */
    public function addGroupBy($collection, $tablePrefix)
    {
        $collection->getSelect()->group("DATE($tablePrefix.created_at)");
    }

    /**
     * @param $collection
     */
    public function addFromFilter($collection)
    {
        $filters = $this->getRequestParams();
        $from = isset($filters['from']) ? $filters['from'] : date('Y-m-d', $this->helper->getDefaultFromDate());
        if ($from) {
            $collection->getSelect()->where('DATE(created_at) >= ?', $from);
        }
    }

    /**
     * @param $collection
     */
    public function addToFilter($collection)
    {
        $filters = $this->getRequestParams();
        $to = isset($filters['to']) ? $filters['to'] : date('Y-m-d');
        if ($to) {
            $collection->getSelect()->where('DATE(created_at) <= ?', $to);
        }
    }
}
