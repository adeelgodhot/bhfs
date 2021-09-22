<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_RulesLoyalty
 */


namespace Amasty\RulesLoyalty\Block;

use Amasty\RulesLoyalty\Helper\Calculator;
use Amasty\RulesLoyalty\Model\ConfigProvider;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Template
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Calculator
     */
    private $calculator;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var array
     */
    private $values = [];

    public function __construct(
        Context $context,
        Calculator $calculator,
        FilterProvider $filterProvider,
        ConfigProvider $configProvider,
        array $data
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->storeManager = $context->getStoreManager();
        $this->calculator = $calculator;
        $this->filterProvider = $filterProvider;
        $this->configProvider = $configProvider;
        parent::__construct($context, $data);
    }

    /**
     * @return Template
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Loyalty Program'));
        return parent::_prepareLayout();
    }

    /**
     * @param string $key
     * @return int|string
     */
    private function getValues($key)
    {
        if (empty($this->values)) {
            $values = [];
            $store = $this->storeManager->getStore();
            $calc = $this->calculator;
            // membership
            $values['membership_days'] = $calc->getMembership();
            // all period
            $allPeriod = $calc->getAllPeriodTotal();
            $values['all_of_placed_orders'] = $allPeriod['of_placed_orders'];
            $values['all_total_orders_amount'] = $calc->convertPrice($allPeriod['total_orders_amount'], $store, true);
            $values['all_average_order_value'] = $calc->convertPrice($allPeriod['average_order_value'], $store, true);
            // this month
            $thisMonth = $calc->getThisMonthTotal();
            $values['this_of_placed_orders'] = $thisMonth['of_placed_orders'];
            $values['this_total_orders_amount'] = $calc->convertPrice($thisMonth['total_orders_amount'], $store, true);
            $values['this_average_order_value'] = $calc->convertPrice($thisMonth['average_order_value'], $store, true);
            // last month
            $lastMonth = $calc->getLastMonthTotal();
            $values['last_of_placed_orders'] = $lastMonth['of_placed_orders'];
            $values['last_total_orders_amount'] = $calc->convertPrice($lastMonth['total_orders_amount'], $store, true);
            $values['last_average_order_value'] = $calc->convertPrice($lastMonth['average_order_value'], $store, true);

            $this->values = $values;
        }

        return isset($this->values[$key]) ? $this->values[$key] : 0;
    }

    /**
     * @return int|string
     */
    public function getMembership()
    {
        return $this->getValues('membership_days');
    }

    /**
     * @return int|string
     */
    public function getOrdersCount()
    {
        return $this->getValues('all_of_placed_orders');
    }

    /**
     * @return int|string
     */
    public function getOrdersAve()
    {
        return $this->getValues('all_average_order_value');
    }

    /**
     * @return int|string
     */
    public function getOrdersAmount()
    {
        return $this->getValues('all_total_orders_amount');
    }

    /**
     * @return int|string
     */
    public function getThisMonthCount()
    {
        return $this->getValues('this_of_placed_orders');
    }

    /**
     * @return int|string
     */
    public function getThisMonthAve()
    {
        return $this->getValues('this_average_order_value');
    }

    /**
     * @return int|string
     */
    public function getThisMonthAmount()
    {
        return $this->getValues('this_total_orders_amount');
    }

    /**
     * @return int|string
     */
    public function getLastMonthCount()
    {
        return $this->getValues('last_of_placed_orders');
    }

    /**
     * @return int|string
     */
    public function getLastMonthAve()
    {
        return $this->getValues('last_average_order_value');
    }

    /**
     * @return int|string
     */
    public function getLastMonthAmount()
    {
        return $this->getValues('last_total_orders_amount');
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->configProvider->getHeader();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->filterProvider->getBlockFilter()->filter($this->configProvider->getDescription());
    }

    /**
     * @return string
     */
    public function getStatsHeader()
    {
        return $this->configProvider->getStatsHeader();
    }
}
