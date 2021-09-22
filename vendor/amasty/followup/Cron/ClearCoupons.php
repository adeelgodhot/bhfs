<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Cron;

use Magento\Framework\App\ResourceConnection;

class ClearCoupons
{
    protected $_dateTime;
    protected $_date;
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $ruleCollectionFactory;

    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ){
        $this->_dateTime = $dateTime;
        $this->_date = $date;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    public function execute()
    {
        $formattedDate = $this->_dateTime->formatDate($this->_date->gmtTimestamp());

        $collection = $this->ruleCollectionFactory->create();

        $collection->getSelect()->joinLeft(
            ['history' => $collection->getTable('amasty_amfollowup_history')],
            'main_table.rule_id = history.sales_rule_id',
            []
        );

        $collection->addFieldToFilter('coupon_to_date', ['lt' => $formattedDate]);

        foreach($collection as $coupon)
        {
            $coupon->delete();
        }
    }
}