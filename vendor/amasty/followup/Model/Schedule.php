<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model;

use Amasty\Followup\Model\Event\Basic;
use Amasty\Followup\Model\Event\BasicFactory;
use Amasty\Followup\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
use Amasty\Followup\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Amasty\Followup\Model\ResourceModel\Schedule as ResourceSchedule;
use Amasty\Followup\Model\ResourceModel\Schedule\Collection as ScheduleCollection;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Logger;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;

class Schedule extends \Magento\Framework\Model\AbstractModel
{
    protected $scheduleCollections = [];
    protected $customerGroup = [];
    protected $rules = [];
    protected $dateTime;
    protected $date;
    protected $basicFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $customerLog = [];

    /**
     * @var HistoryCollectionFactory
     */
    protected $historyCollectionFactory;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var GroupRepository
     */
    protected $groupRepository;

    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        ResourceSchedule $resource,
        ScheduleCollection $resourceCollection,
        StoreManagerInterface $storeManager,
        DateTime\DateTime $date,
        DateTime $dateTime,
        BasicFactory $basicFactory,
        HistoryCollectionFactory $historyCollectionFactory,
        HistoryFactory $historyFactory,
        Logger $logger,
        GroupRepository $groupRepository,
        RuleCollectionFactory $ruleCollectionFactory,
        RuleFactory $ruleFactory,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->date = $date;
        $this->basicFactory = $basicFactory;
        $this->historyFactory = $historyFactory;
        $this->logger = $logger;
        $this->groupRepository = $groupRepository;
        $this->ruleCollectionFactory = $ruleCollectionFactory;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );

        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->ruleFactory = $ruleFactory;
    }

    public function _construct()
    {
        $this->_init(ResourceSchedule::class);
    }

    public function getConfig()
    {
        $config = $this->getData();
        unset($config['rule_id']);
        $config['days'] = $this->getDays();
        $config['hours'] = $this->getHours();
        $config['minutes'] = $this->getMinutes();
        $config['discount_amount'] = $config['discount_amount'] * 1;
        $config['discount_qty'] = $config['discount_qty'] * 1;

        return $config;
    }

    public function getDeliveryTime()
    {
        return ($this->getDays() * 24 * 60 * 60) +
            ($this->getHours() * 60 * 60) +
            ($this->getMinutes() * 60);
    }

    protected function getScheduleCollection($rule)
    {
        if (!isset($this->scheduleCollections[$rule->getId()])) {
            $this->scheduleCollections[$rule->getId()] = $this
                ->getCollection()
                ->addRule($rule);
        }

        return $this->scheduleCollections[$rule->getId()];
    }

    public function getCustomerEmailVars($customer, $history)
    {
        $logCustomer = $this->loadCustomerLog($customer);
        $customerGroup = $this->loadCustomerGroup($customer->getGroupId());
        $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();
        return [
            Formatmanager::TYPE_CUSTOMER => $customer,
            Formatmanager::TYPE_CUSTOMER_GROUP => $customerGroup,
            Formatmanager::TYPE_CUSTOMER_LOG => $logCustomer,
            Formatmanager::TYPE_HISTORY => $history,
            Formatmanager::TYPE_CUSTOMER_NAME => $customerName,
            Formatmanager::TYPE_CUSTOMER_GROUP_CODE => $customerGroup->getCode(),
            Formatmanager::TYPE_HISTORY_COUPON_CODE => $history->getCouponCode()
        ];
    }

    protected function loadCustomerLog($customer)
    {
        $customerId = $customer->getId();

        if (!isset($this->customerLog[$customerId])) {
            $this->customerLog[$customerId] = $this->logger->get($customerId);
        }

        return $this->customerLog[$customerId];
    }

    protected function loadCustomerGroup($id)
    {
        if (!isset($this->customerGroup[$id])) {
            $this->customerGroup[$id] = $this->groupRepository->getById($id);
        }

        return $this->customerGroup[$id];
    }

    /**
     * @param Rule $rule
     * @param Basic $event
     * @param \Magento\Customer\Model\Customer $customer
     * @param null $product
     *
     * @return array
     * @throws \Magento\Framework\Exception\MailException
     */
    public function createCustomerHistory($rule, $event, $customer, $product = null)
    {
        $customerHistory = [];
        $scheduleCollection = $this->getScheduleCollection($rule);
        $scheduledAndCreatedAt = null;

        foreach ($scheduleCollection as $schedule) {
            $this->storeManager->setCurrentStore($customer->getStoreId());
            $history = $this->historyFactory->create();
            $history->initCustomerItem($customer);

            if ($product instanceof Product) {
                $scheduledAndCreatedAt = $product->getSpecialFromDate();
            }

            $history->createItem($schedule, $scheduledAndCreatedAt, $scheduledAndCreatedAt);

            if ($product === null) {
                $email = $event->getEmail($schedule, $history, $this->getCustomerEmailVars($customer, $history));
                $history->saveEmail($email);
            }

            $customerHistory[] = $history;
        }

        return $customerHistory;
    }

    /**
     * @param Rule $rule
     * @param Basic $event
     * @param Order $order
     * @param Quote $quote
     * @param Customer $customer
     *
     * @return array
     * @throws \Exception
     */
    public function createOrderHistory($rule, $event, $order, $quote, $customer)
    {
        $orderHistory = [];
        $scheduleCollection = $this->getScheduleCollection($rule);

        if (!$customer->getId()) {
            $this->initCustomer($customer, $order);
            $quote->setCustomerId(null);
        }

        foreach ($scheduleCollection as $schedule) {
            $this->storeManager->setCurrentStore($quote->getStoreId());
            $history = $this->historyFactory->create();
            $historyItems = $this->getScheduledOrderHistory(
                $order,
                $schedule,
                $schedule->getRuleId(),
                $quote->getStoreId()
            );

            if ($historyItems) {
                foreach ($historyItems as $item) {
                    $orderHistory[] = $item;
                }

                continue;
            }

            $history->initOrderItem($order, $quote);
            $history->createItem($schedule, $quote->getCreatedAt());
            $email = $event->getEmail(
                $schedule,
                $history,
                $this->getOrderEmailVars($order, $quote, $customer, $history)
            );

            if (!$email) {
                if ($history->getStatus() != History::STATUS_NO_CROSSEL_PRODUCT) {
                    $history->setStatus(History::STATUS_NO_PRODUCT);
                    $history->save();
                }
            } else {
                $history->saveEmail($email);
                $orderHistory[] = $history;
            }
        }

        return $orderHistory;
    }

    /**
     * @param Order $order
     * @param Schedule $schedule
     * @param int $ruleId
     * @param int $storeId
     *
     * @return bool|\Magento\Framework\DataObject[]
     */
    public function getScheduledOrderHistory($order, $schedule, $ruleId, $storeId)
    {
        $historyCollection = $this->historyCollectionFactory->create();
        $historyCollection->getOrderFilter($order->getId(), $schedule->getScheduleId(), $ruleId, $storeId);

        return $historyCollection->getSize() ? $historyCollection->getItems() : false;
    }

    public function initCustomer($customer, $order)
    {
        $data = $order->getBillingAddress()->getData();

        foreach ($data as $key => $val) {
            if ($key !== 'entity_id' && $key !== 'parent_id') {
                $customer->setData($key, $val);
            }
        }

        $customer->setData('group_id', $order->getCustomerGroupId());
    }

    protected function getOrderEmailVars($order, $quote, $customer, $history)
    {
        $vars = $this->getCustomerEmailVars($customer, $history);
        $vars[Formatmanager::TYPE_ORDER] = $order;
        $vars[Formatmanager::TYPE_ORDER_STATUS] = $order->getStatusLabel();
        $vars[Formatmanager::TYPE_ORDER_STATUS] = $order->getIncrementId();
        $vars[Formatmanager::TYPE_ORDER_SHIPPING_METHOD] = $order->getShippingDescription();
        $vars[Formatmanager::TYPE_QUOTE] = $quote;

        return $vars;
    }

    public function getDays()
    {
        return $this->getDelayedStart() > 0 && floor($this->getDelayedStart() / 24 / 60 / 60) ?
            floor($this->getDelayedStart() / 24 / 60 / 60) :
            null;
    }

    public function getHours()
    {
        $days = $this->getDays();
        $time = $this->getDelayedStart() - ($days * 24 * 60 * 60);

        return $time > 0 ?
            floor($time / 60 / 60) :
            null;
    }

    public function getMinutes()
    {
        $days = $this->getDays();
        $hours = $this->getHours();
        $time = $this->getDelayedStart() - ($days * 24 * 60 * 60) - ($hours * 60 * 60);

        return $time > 0 ?
            floor($time / 60) :
            null;
    }

    public function checkCustomerRules($customer, $types = [], $product = null)
    {
        $ruleCollection = $this->getRuleCollection($types);

        foreach ($ruleCollection as $rule) {
            $event = $rule->getStartEvent();

            if ($event->validate($customer)) {
                $this->createCustomerHistory($rule, $event, $customer, $product);
            }
        }
    }

    public function getRuleCollection($types = [])
    {
        $ruleCollection = $this->ruleCollectionFactory->create()->addStartFilter($types);

        return $ruleCollection;
    }

    public function checkSubscribtionRules($subscriber, $customer, $types = [])
    {
        $ruleCollection = $this->getRuleCollection($types);

        foreach ($ruleCollection as $rule) {
            $event = $rule->getStartEvent();

            if ($event->validateSubscription($subscriber, $customer)) {
                $this->createCustomerHistory($rule, $event, $customer);
            }
        }
    }

    public function process()
    {
        $historyCollection = $this->getHistoryCollection()
            ->addReadyFilter($this->dateTime->formatDate($this->basicFactory->create()->getCurrentExecution()));

        foreach ($historyCollection as $history) {
            $rule = $this->loadRule($history->getRuleId());

            if ($history->validateBeforeSent($rule)) {
                $history->processItem($rule, $history->getEmail());
            } else {
                $history->cancelItem();
            }
        }
    }

    protected function getHistoryCollection()
    {
        $historyCollection = $this->historyCollectionFactory->create()->addOrderData();

        return $historyCollection;
    }

    protected function loadRule($ruleId)
    {
        if (!isset($this->rules[$ruleId])) {
            $this->rules[$ruleId] = $this->ruleFactory->create()->load($ruleId);
        }

        return $this->rules[$ruleId];
    }
}
