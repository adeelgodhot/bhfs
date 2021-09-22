<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Reports
 */


namespace Amasty\Reports\Model\ResourceModel\Sales\Orders;

use Amasty\Reports\Traits\Filters;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
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

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\RequestInterface $request, // TODO move it out of here
        \Amasty\Reports\Helper\Data $helper,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $coreResourceHelper,
            $connection,
            $resource
        );
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * @param \Amasty\Reports\Model\ResourceModel\Sales\Orders\Grid\Collection $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param $collection
     */
    public function applyBaseFilters($collection)
    {
        $this->joinSalesOrderItem($collection);
        $collection->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'total_orders' => 'COUNT(main_table.entity_id)',
                'total_items' => 'ROUND(SUM(main_table.total_qty_ordered))',
                'subtotal' => 'SUM(main_table.base_subtotal)',
                'tax' => 'SUM(main_table.base_tax_amount)',
                'status' => 'main_table.status',
                'shipping' => 'SUM(main_table.base_shipping_amount)',
                'discounts' => 'SUM(main_table.base_discount_amount)',
                'total' => 'SUM(main_table.base_grand_total)',
                'invoiced' => 'IFNULL(SUM(main_table.base_total_invoiced), 0)',
                'refunded' => 'IFNULL(SUM(main_table.base_total_refunded), 0)',
                'entity_id' => 'CONCAT(main_table.entity_id,\''.$this->createUniqueEntity().'\')',
                'cost' => $this->getCostSelect(),
                'profit' => sprintf(
                    '(SUM(main_table.base_subtotal) + SUM(main_table.base_discount_amount) - %s)',
                    $this->getCostSelect()
                )
            ]);
    }

    private function getCostSelect(): string
    {
        return '(IF(SUM(sales_order_items.cost), SUM(sales_order_items.cost), 0))';
    }

    /**
     * @param $collection
     */
    public function applyToolbarFilters($collection)
    {
        $this->addFromFilter($collection);
        $this->addToFilter($collection);
        $this->addStoreFilter($collection);
        $this->addGroupFilter($collection);
        $this->addStatusFilter($collection);
    }

    /**
     * @param AbstractCollection $collection
     */
    private function joinSalesOrderItem($collection)
    {
        $salesOrderItem = $this->getConnection()
            ->select()
            ->from(
                $this->getTable('sales_order_item'),
                [
                    'order_id' => 'order_id',
                    'cost' => 'SUM(base_cost)'
                ]
            )
            ->where('product_type = "simple"')
            ->group('order_id');

        $collection->getSelect()->joinLeft(
            ['sales_order_items' => $salesOrderItem],
            'main_table.entity_id = sales_order_items.order_id'
        );
    }

    /**
     * @param $collection
     */
    protected function addGroupFilter($collection)
    {
        $filters = $this->getRequestParams();
        $group = isset($filters['type']) ? $filters['type'] : 'overview';
        switch ($group) {
            case 'overview':
                $collection->getSelect()
                    ->columns([
                        'period' => "DATE(main_table.created_at)",
                    ]);
                $collection->getSelect()->group('DATE(main_table.created_at)');
                break;
            case 'status':
                $collection->getSelect()->columns([
                    'period' => "status",
                ]);
                $collection->getSelect()->group('status');
                break;
        }
    }
}
