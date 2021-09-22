<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

declare(strict_types=1);

namespace Amasty\Followup\Block\Adminhtml\Rule\Edit\Tab\Test;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory;

class Order extends \Magento\Reports\Block\Adminhtml\Grid\Shopcart
{
    /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var Config
     */
    private $orderConfig;

    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $orderCollectionFactory,
        Config $orderConfig,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper);

        $this->setId('amasty_followup_rule_test');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);

        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderConfig = $orderConfig;
    }

    protected function _prepareCollection()
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->getSelect()->joinInner(
            ['quote' => $collection->getTable('quote')],
            'main_table.increment_id = quote.reserved_order_id',
            []
        );
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _addColumns()
    {
        $this->addColumn(
            'run',
            [
                'header' => '',
                'index' =>'customer_id',
                'sortable' => false,
                'filter' => false,
                'renderer' => 'Amasty\Followup\Block\Adminhtml\Rule\Edit\Tab\Test\Renderer\Run',
                'align' => 'center',
            ]
        );

        $this->addColumn(
            'real_order_id',
            [
                'header'=> __('Order #'),
                'width' => '80px',
                'type' => 'text',
                'index' => 'increment_id',
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                [
                    'header' => __('Purchased From (Store)'),
                    'index' => 'store_id',
                    'type' => 'store',
                    'store_view' => true,
                    'display_deleted' => true,
                ]
            );
        }

        $this->addColumn(
            'created_at',
            [
                'header' => __('Purchased On'),
                'index' => 'created_at',
                'type' => 'datetime',
                'width' => '100px',
            ]
        );

        $this->addColumn(
            'billing_name',
            [
                'header' => __('Bill to Name'),
                'index' => 'billing_name',
            ]
        );

        $this->addColumn(
            'shipping_name',
            [
                'header' => __('Ship to Name'),
                'index' => 'shipping_name',
            ]
        );

        $this->addColumn(
            'base_grand_total',
            [
                'header' => __('G.T. (Base)'),
                'index' => 'base_grand_total',
                'type'  => 'currency',
                'currency' => 'base_currency_code',
            ]
        );

        $this->addColumn(
            'grand_total',
            [
                'header' => __('G.T. (Purchased)'),
                'index' => 'grand_total',
                'type'  => 'currency',
                'currency' => 'order_currency_code',
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'width' => '70px',
                'options' => $this->orderConfig->getStatuses(),
            ]
        );
    }

    protected function _prepareColumns()
    {
        $this->_addColumns();

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}
