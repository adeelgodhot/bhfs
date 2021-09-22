<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Observer;

use Amasty\Followup\Model\Rule as Rule;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class NewsletterSubscriber implements ObserverInterface
{
    /**
     * @var \Amasty\Followup\Model\ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var bool
     */
    protected static $onNewsletterSubscriberSaveAfterChecked = false;

    /**
     * @var bool
     */
    public static $isFirstSubscribe = false;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Amasty\Followup\Model\ScheduleFactory $scheduleFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->customerFactory = $customerFactory;
        $this->date = $date;
        $this->storeManager = $storeManager;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        if (!self::$isFirstSubscribe) {
            return;
        }

        $subscriber = $observer->getSubscriber();

        if (!self::$onNewsletterSubscriberSaveAfterChecked) {
            $customer = $this->customerFactory->create();
            $websiteId = $this->storeManager->getStore($subscriber->getStoreId())->getWebsiteId();
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($subscriber->getEmail());

            if (!$customer->getId()) {
                $customer->addData(
                    [
                        'email' => $subscriber->getSubscriberEmail(),
                        'store_id' => $subscriber->getStoreId(),
                        'group_id' => GroupManagement::NOT_LOGGED_IN_ID
                    ]
                );
            }

            if ($subscriber->getChangeStatusAt()
                && $subscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED
            ) {
                $subscriber->setSubscriberStatus(\Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED);
            }

            $this->scheduleFactory->create()->checkSubscribtionRules(
                $subscriber,
                $customer,
                [Rule::TYPE_CUSTOMER_SUBSCRIPTION]
            );

            self::$onNewsletterSubscriberSaveAfterChecked = true;
            $subscriber->setChangeStatusAt($this->date->date("Y-m-d H:i:s"));
            $subscriber->save();
        }
    }
}
