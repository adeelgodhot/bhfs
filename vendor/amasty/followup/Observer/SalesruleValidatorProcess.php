<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Observer;

use Amasty\Followup\Model\HistoryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class SalesruleValidatorProcess implements ObserverInterface
{
    protected $historyFactory;
    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        HistoryFactory $historyFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->historyFactory = $historyFactory;
    }

    public function execute(EventObserver $observer)
    {
        if ($this->scopeConfig->getValue('amfollowup/general/customer_coupon')) {
            $salesRule = $observer->getEvent()->getRule();
            $coupon = $observer->getEvent()->getQuote()->getCouponCode();
            $history = $this->historyFactory->create()
                ->getCollection()
                ->addFieldToFilter('sales_rule_id', $salesRule->getId())
                ->addFieldToFilter('coupon_code', $coupon)
                ->getFirstItem();

            if ($history->getId()) {
                $customerEmail = $observer->getEvent()->getQuote()->getCustomer()->getEmail() ?
                    $observer->getEvent()->getQuote()->getCustomer()->getEmail() :
                    $observer->getEvent()->getQuote()->getBillingAddress()->getEmail();

                if ($customerEmail != $history->getEmail()) {
                    $observer->getEvent()->getQuote()->setCouponCode("");
                }
            }
        }
    }
}
