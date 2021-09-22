<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model;

use Amasty\Base\Model\Serializer;
use Amasty\Followup\Model\Event\Basic;
use Amasty\Followup\Model\Mail\MessageBuilder\MessageBuilder;
use Amasty\Followup\Model\Mail\MessageBuilder\MessageBuilderFactory;
use Amasty\Followup\Model\BlacklistFactory;
use Amasty\Followup\Model\ResourceModel\History\Collection;
use Amasty\Followup\Model\RuleFactory;
use Amasty\Followup\Model\ScheduleFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\MessageFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\SalesRule\Model\Coupon\Massgenerator;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory;
use Magento\SalesRule\Model\Rule\Condition\Combine;
use Magento\SalesRule\Model\RuleFactory as SalesRuleFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class History extends \Magento\Framework\Model\AbstractModel
{
    /**
     * History status values
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SENT = 'sent';
    const STATUS_CANCEL = 'cancel';
    const STATUS_NO_PRODUCT = 'no_product';
    const STATUS_NO_CROSSEL_PRODUCT = 'no_crossel_products';
    /**
     * Cancel reason value
     */
    const REASON_BLACKLIST = 'blacklist';
    const REASON_EVENT = 'event';
    const REASON_ADMIN = 'admin';
    const REASON_NOT_SUBSCRIBED = 'not_subsribed';
    /**
     * XML template path values
     */
    const NAME_XML_PATH = 'amfollowup/template/name';
    const EMAIL_XML_PATH = 'amfollowup/template/email';
    const CC_XML_PATH = 'amfollowup/template/cc';

    /**
     * @var array
     */
    protected $cancelEventValidation = [];

    /**
     * @var array
     */
    protected $cancelNotSubscribedValidation = [];

    /**
     * @var array
     */
    protected $cancelBlacklistValidation = [];

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var DateTime\DateTime
     */
    protected $date;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreInterface
     */
    protected $store;

    /**
     * @var FactoryInterface
     */
    protected $templateFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var Event\Basic
     */
    protected $basicFactory;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var TransportInterfaceFactory
     */
    protected $mailTransportFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CollectionFactory
     */
    protected $couponCollectionFactory;

    /**
     * @var ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var Massgenerator
     */
    protected $massgenerator;

    /**
     * @var BlacklistFactory
     */
    protected $blacklistFactory;

    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var SalesRuleFactory
     */
    protected $salesRuleFactory;

    /**
     * @var MessageBuilder
     */
    private $messageBuilder;

    public function __construct(
        Context $context,
        Registry $registry,
        DateTime\DateTime $date,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        TransportInterfaceFactory $mailTransportFactory,
        FactoryInterface $templateFactory,
        MessageFactory $messageFactory,
        Basic $basicFactory,
        ScopeConfigInterface $scopeConfig,
        ResourceModel\History $resource = null,
        Collection $resourceCollection = null,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Serializer $serializer,
        CustomerRepository $customerRepository,
        CollectionFactory $couponCollectionFactory,
        ScheduleFactory $scheduleFactory,
        RuleFactory $ruleFactory,
        Massgenerator $massgenerator,
        BlacklistFactory $blacklistFactory,
        SubscriberFactory $subscriberFactory,
        SalesRuleFactory $salesRuleFactory,
        MessageBuilderFactory $messageBuilderFactory,
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        $this->date = $date;
        $this->storeManager = $storeManager;
        $this->templateFactory = $templateFactory;
        $this->scopeConfig = $scopeConfig;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->basicFactory = $basicFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection);
        $this->messageFactory = $messageFactory;
        $this->mailTransportFactory = $mailTransportFactory;
        $this->serializer = $serializer;
        $this->customerRepository = $customerRepository;
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->scheduleFactory = $scheduleFactory;
        $this->ruleFactory = $ruleFactory;
        $this->massgenerator = $massgenerator;
        $this->blacklistFactory = $blacklistFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->salesRuleFactory = $salesRuleFactory;
        $this->messageBuilder = $messageBuilderFactory->create();
    }

    public function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Followup\Model\ResourceModel\History::class);
    }

    public function processItem($rule, $email = null, $testMode = false)
    {
        $this->setExecutedAt($this->dateTime->formatDate($this->basicFactory->getCurrentExecution()));
        $this->setStatus(self::STATUS_PROCESSING);
        $this->save();

        if ($this->sendEmail($rule, $email, $testMode)) {
            $this->setFinishedAt($this->dateTime->formatDate($this->basicFactory->getCurrentExecution()));
            $this->setStatus(self::STATUS_SENT);
            $this->save();
        }
    }

    protected function sendEmail($rule, $email = null, $testMode = false)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $recipient = $this->scopeConfig->getValue('amfollowup/test/recipient');
        $safeMode = $this->scopeConfig->getValue('amfollowup/test/safe_mode');
        $to = $email;

        if ((int)$safeMode == 1 || $testMode) {
            $to = $recipient;
        }

        $name = $this->getCustomerName();

        $senderName = $rule->getSenderName()
            ? $rule->getSenderName()
            : $this->scopeConfig->getValue(self::NAME_XML_PATH, ScopeInterface::SCOPE_STORE, $storeId);
        $senderEmail = $rule->getSenderEmail()
            ? $rule->getSenderEmail()
            : $this->scopeConfig->getValue(self::EMAIL_XML_PATH, ScopeInterface::SCOPE_STORE, $storeId);
        $cc = $rule->getSenderCc()
            ? $rule->getSenderCc()
            : $this->scopeConfig->getValue(self::CC_XML_PATH, ScopeInterface::SCOPE_STORE, $storeId);
        $message = $this->messageFactory->create();

        if ($this->getBody() === null) {
            $event = $rule->getStartEvent();
            $schedule = $this->getSchedule();
            $customer = $this->customerRepository->getById($this->getCustomerId());
            $email = $event->getEmail($schedule, $this, $schedule->getCustomerEmailVars($customer, $this));
            $this->saveEmail($email);
        }

        $message->addTo($to, $name)
            ->setSubject($this->getSubject());

        if (method_exists($message, 'setFromAddress')) {
            $message->setFromAddress($senderEmail, $senderName);
        } else {
            $message->setFrom($senderEmail, $senderName);
        }

        if (method_exists($message, 'setBodyHtml')) {
            $message->setBodyHtml($this->getBody());
        } else {
            $message
                ->setMessageType(\Magento\Framework\Mail\MessageInterface::TYPE_HTML)
                ->setBody($this->getBody());
        }

        if (!empty($cc) && !$safeMode && !$testMode) {
            // Split on commas, trim all values, and finally filter out all FALSE values
            $emailsCopyTo = array_filter(array_map('trim', explode(',', $cc)));
            $message->addBcc($emailsCopyTo);
        }

        // This is a compatibility fill for the implemented EmailMessageInterface in Magento 2.3.3.
        $message = $this->messageBuilder->build($message);
        $mailTransport = $this->mailTransportFactory->create(['message' => clone $message]);
        $mailTransport->sendMessage();

        return true;
    }
    
    public function getSchedule()
    {
        return $this->scheduleFactory->create()->load($this->getScheduleId());
    }
    
    public function initOrderItem($order, $quote)
    {
        $this->addData(
            [
                'order_id' => $order->getId(),
                'increment_id' => $order->getIncrementId(),
                'store_id' => $quote->getStoreId(),
                'email' => $quote->getCustomerEmail(),
                'customer_id' => $quote->getCustomerId(),
                'customer_name' => $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname()
            ]
        );
        
        return $this;
    }
    
    public function initCustomerItem($customer)
    {
        $this->addData(
            [
                'store_id' => $customer->getStoreId(),
                'email' => $customer->getEmail(),
                'customer_id' => $customer->getId(),
                'customer_name' => $customer->getFirstname() . ' ' . $customer->getLastname()
            ]
        );
        
        return $this;
    }
    
    protected function getCoupon($rule, $schedule)
    {
        $coupon = [
            'code' => null,
            'id' => null
        ];

        if ($schedule->getUseRule()) {
            $salesCoupon = $this->generateCouponPool($rule);
            $coupon['id'] = $salesCoupon->getId();
            $coupon['code'] = $salesCoupon->getCode();
        } elseif ($rule) {
            $coupon['code'] = $rule->getCouponCode();
        }

        return $coupon;
    }

    protected function generateCouponPool(\Magento\SalesRule\Model\Rule $rule)
    {
        $salesCoupon = null;
        $generator = $rule->getCouponCodeGenerator();
        $generator = $this->massgenerator;
        $generator->setData([
            'rule_id' => $rule->getId(),
            'qty' => 1,
            'length' => 12,
            'format' => 'alphanum',
            'prefix' => '',
            'suffix' => '',
            'dash' => '0',
            'uses_per_coupon' => $rule->getUsesPerCoupon(),
            'usage_per_customer' => $rule->getUsesPerCustomer(),
            'to_date' => '',
        ]);
        $generator->generatePool();
        $generated = $generator->getGeneratedCount();
        $resourceCoupon = $this->couponCollectionFactory->create();
        $resourceCoupon
            ->addFieldToFilter('main_table.rule_id', $rule->getId())
            ->getSelect()
            ->joinLeft(
                ['h' => $resourceCoupon->getTable('amasty_amfollowup_history')],
                'main_table.coupon_id = h.coupon_id',
                []
            )->where('h.history_id is null')
            ->order('main_table.coupon_id desc')
            ->limit(1);
        $items = $resourceCoupon->getItems();

        if (count($items) > 0) {
            $salesCoupon = end($items);
        }

        return $salesCoupon;
    }
    
    public function createItem($schedule, $createdAt = null, $scheduledAt = null)
    {
        $rule = $this->getRule($schedule);
        $coupon = $this->getCoupon($rule, $schedule);

        $createdAt =  $createdAt
            ? $createdAt
            : $this->dateTime->formatDate($this->basicFactory->getCurrentExecution());

        $scheduledAt = $scheduledAt
            ? strtotime($scheduledAt) + $schedule->getDelayedStart()
            : strtotime($createdAt) + $schedule->getDelayedStart();

        $this->addData([
           'public_key' => uniqid(),
           'schedule_id' => $schedule->getId(),
           'rule_id' => $schedule->getRuleId(),
           'created_at' => $createdAt,
           'scheduled_at' => $this->dateTime->formatDate($scheduledAt),
           'status' => self::STATUS_PENDING,
           'sales_rule_id' => $rule ? $rule->getId() : null,
           'coupon_code' => $coupon['code'],
           'coupon_id' => $coupon['id'],
           'coupon_to_date' => $rule ? $rule->getToDate() : null,
        ]);
        $this->save();
        
        return $this;
    }
    
    public function saveEmail($email = [])
    {
        $this->addData([
            'subject' => $email['subject'],
            'body' => $email['body'],
        ]);
        $this->save();
        
        return $this;
    }

    protected function getRule($schedule)
    {
        $rule = null;

        if ($schedule->getUseRule()) {
            $rule = $this->salesRuleFactory->create()->load($schedule->getSalesRuleId());
        } elseif ($schedule->getCouponType()) {
            $store = $this->storeManager->getStore($this->getStoreId());
            $rule = $this->ruleFactory->create()->load($schedule->getRuleId());
            $rule = $this->createCoupon(
                $store,
                $schedule,
                $rule
            );
        }
        
        return $rule;
    }

    protected function getCouponToDate($days, $delayedStart)
    {
        return $this->dateTime->formatDate(
            $this->date->gmtTimestamp()
            + $days * 24 * 3600
            + $delayedStart
        );
    }

    protected function createCoupon($store, $schedule, $rule)
    {
        $salesRule = $this->salesRuleFactory->create();
        $salesRule->setData([
            'name' => 'Amasty: Followup Coupon #' . $this->getEmail(),
            'is_active' => '1',
            'website_ids' => [0 => $store->getWebsiteId()],
            'customer_group_ids' => $this->getGroupsIds($rule),
            'coupon_code' => strtoupper(uniqid()),
            'uses_per_coupon' => 1,
            'coupon_type' => 2,
            'from_date' => '',
            'to_date' => $this->getCouponToDate($schedule->getExpiredInDays(), $schedule->getDeliveryTime()),
            'uses_per_customer' => 1,
            'simple_action' => $schedule->getCouponType(),
            'discount_amount' => $schedule->getDiscountAmount(),
            'stop_rules_processing' => '0',
        ]);

        if ($schedule->getDiscountQty() > 0) {
            $salesRule->setDiscountQty($schedule->getDiscountQty());
        }

        if ($schedule->getDiscountStep() > 0) {
            $salesRule->setDiscountStep($schedule->getDiscountStep());
        }

        $salesRule->setConditionsSerialized($this->serializer->serialize($this->getConditions($rule)));
        $salesRule->save();

        return $salesRule;
    }

    protected function getConditions(Rule $rule)
    {
        $salesRuleConditions = [];
        $conditions = $rule->getSalesRule()->getConditions()->asArray();

        if (isset($conditions['conditions'])) {
            foreach ($conditions['conditions'] as $idx => $condition) {
                $salesRuleConditions[] = $condition;
            }
        }

        return [
            'type' => Combine::class,
            'attribute' => '',
            'operator' => '',
            'value' => '1',
            'is_value_processed' => '',
            'aggregator' => 'all',
            'conditions' => $salesRuleConditions
        ];
    }

    protected function getGroupsIds(Rule $rule)
    {
        $groupsIds = [];
        $strGroupIds = $rule->getCustGroups();

        if (!empty($strGroupIds)) {
            $groupsIds = explode(',', $strGroupIds);
        } else {
            $groupList = $this->groupRepository->getList($this->searchCriteriaBuilder->create());

            foreach ($groupList->getItems() as $group) {
                $groupsIds[] = $group->getId();
            }
        }

        return $groupsIds;
    }
    
    public function massCancel($ids)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('history_id', ['in' => $ids]);

        foreach ($collection as $history) {
            $history->reason = Amasty_Followup_Model_History::REASON_ADMIN;
            $history->status = Amasty_Followup_Model_History::STATUS_CANCEL;
            $history->save();
        }
    }

    protected function validateBlacklist($history)
    {
        if (!isset($this->cancelBlacklistValidation[$history->getEmail()])) {
            $blist = $this->blacklistFactory->create()->load($history->getEmail(), 'email');
            $this->cancelBlacklistValidation[$history->getEmail()] = $blist->getId() === null;
        }
        
        return $this->cancelBlacklistValidation[$history->getEmail()];
    }
    
    protected function validateNotSubscribed($rule, $history)
    {
        if (!isset($this->cancelNotSubscribedValidation[$rule->getId()])) {
            $this->cancelNotSubscribedValidation[$rule->getId()] = [];
        }
        
        if (!isset($this->cancelNotSubscribedValidation[$rule->getId()][$history->getCustomerId()])) {
            $subscriber = $this->subscriberFactory->create()->loadByCustomerId($history->getCustomerId());
            $this->cancelNotSubscribedValidation[$rule->getId()][$history->getCustomerId()] =
                $subscriber->getSubscriberStatus() != Subscriber::STATUS_SUBSCRIBED;
        }
        
        return $this->cancelNotSubscribedValidation[$rule->getId()][$history->getCustomerId()];
    }

    protected function validateCancelEvent($rule, $history)
    {
        if (!isset($this->cancelEventValidation[$rule->getId()])) {
            $this->cancelEventValidation[$rule->getId()] = [];
        }
        
        if (!isset($this->cancelEventValidation[$rule->getId()][$history->getEmail()])) {
            $cancelEvents = $rule->getCancelEvents();

            if ($cancelEvents) {
                foreach ($cancelEvents as $event) {
                    if ($event->validate($history)) {
                        $this->cancelEventValidation[$rule->getId()][$history->getEmail()] = true;

                        break;
                    } else {
                        $this->cancelEventValidation[$rule->getId()][$history->getEmail()] = false;
                    }
                }
            }
        }
        
        if (!isset($this->cancelEventValidation[$rule->getId()][$history->getEmail()])) {
            $this->cancelEventValidation[$rule->getId()][$history->getEmail()] = false;
        }

        return $this->cancelEventValidation[$rule->getId()][$history->getEmail()];
    }
    
    public function validateBeforeSent($rule)
    {
        if (!$this->validateBlacklist($this)) {
            $this->setReason(self::REASON_BLACKLIST);
        } elseif ($rule->getToSubscribers() && $this->validateNotSubscribed($rule, $this)) {
            $this->setReason(self::REASON_NOT_SUBSCRIBED);
        } elseif ($this->validateCancelEvent($rule, $this)) {
            $this->setReason(self::REASON_EVENT);
        }
        
        return !$this->getReason();
    }
    
    public function cancelItem()
    {
        $this->setStatus(self::STATUS_CANCEL);
        $this->save();
    }

    public function getStore($storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->getStoreId();
        }

        if (!$this->store) {
            $this->store = $this->storeManager->getStore($storeId);
        }

        return $this->store;
    }
}
