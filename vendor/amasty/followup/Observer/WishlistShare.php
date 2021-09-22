<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Amasty\Followup\Model\Rule as Rule;

class WishlistShare implements ObserverInterface
{
    protected $_scheduleFactory;
    protected $_customerFactory;

    public function __construct(
        \Amasty\Followup\Model\ScheduleFactory $scheduleFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_scheduleFactory = $scheduleFactory;
        $this->_customerFactory = $customerFactory;
    }

    public function execute(EventObserver $observer)
    {
        $wishlist = $observer->getWishlist();
        $customer = $this->_customerFactory->create()->load($wishlist->getCustomerId());

        $this->_scheduleFactory->create()->checkCustomerRules($customer, array(
            Rule::TYPE_CUSTOMER_WISHLIST_SHARED
        ));
    }
}