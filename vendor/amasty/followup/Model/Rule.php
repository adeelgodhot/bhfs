<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model;

use Amasty\Followup\Api\RuleInterface;
use \Magento\Sales\Model\Order as SalesOrder;

class Rule extends \Magento\SalesRule\Model\Rule implements RuleInterface
{
    /**
     * @var \Amasty\Followup\Model\SalesRule
     */
    protected $_salesRule;

    /**
     * @var \Amasty\Followup\Model\ResourceModel\Schedule\Collection
     */
    protected $_scheduleCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var EventCreator
     */
    private $eventCreator;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Coupon\CodegeneratorFactory $codegenFactory,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $condCombineFactory,
        \Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory $condProdCombineF,
        \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $couponCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Followup\Model\ResourceModel\Rule $resource,
        \Amasty\Followup\Model\ResourceModel\Rule\Collection $resourceCollection,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Amasty\Followup\Model\EventCreator $eventCreator,
        $data = []
    ) {
        $this->_localeDate = $localeDate;
        $this->objectManager = $objectManager;
        $this->eventCreator = $eventCreator;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $couponFactory,
            $codegenFactory,
            $condCombineFactory,
            $condProdCombineF,
            $couponCollection,
            $storeManager,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function _construct()
    {
        $this->_init(\Amasty\Followup\Model\ResourceModel\Rule::class);
    }

    /**
     * @return SalesRule
     */
    public function getSalesRule()
    {
        if (!$this->_salesRule) {
            $this->_salesRule = $this->objectManager
                ->create(\Amasty\Followup\Model\SalesRule::class)->load($this->getId());
        }

        return $this->_salesRule;
    }

    /**
     * @return bool
     */
    public function isOrderRelated()
    {
        return in_array(
            $this->getStartEventType(),
            [
                self::TYPE_ORDER_NEW,
                self::TYPE_ORDER_SHIP,
                self::TYPE_ORDER_INVOICE,
                self::TYPE_ORDER_COMPLETE,
                self::TYPE_ORDER_CANCEL,
            ]
        );
    }

    /**
     * @return ResourceModel\Schedule\Collection
     */
    public function getScheduleCollection()
    {
        if (!$this->_scheduleCollection) {
            $this->_scheduleCollection = $this->objectManager
                ->create(\Amasty\Followup\Model\ResourceModel\Schedule\Collection::class)
                ->addFieldToFilter('rule_id', $this->getId());
        }

        return $this->_scheduleCollection;
    }

    /**
     * @return Event\Basic
     */
    public function getStartEvent()
    {
        if ($this->getStartEventType()) {
            return $this->eventCreator->create($this->getStartEventType(), ['rule' => $this]);
        }

        return $this->eventCreator->create(self::TYPE_BASIC);
    }

    /**
     * @return void
     */
    public function saveSchedule()
    {
        $schedule = $this->getSchedule();

        $savedIds = [];

        if (is_array($schedule) && count($schedule) > 0) {
            foreach ($schedule as $config) {
                $object = $this->objectManager
                    ->create(\Amasty\Followup\Model\Schedule::class);

                if (isset($config['schedule_id'])) {
                    $object->load($config['schedule_id']);
                }

                $deliveryTime = $config['delivery_time'];
                $days = $deliveryTime['days'];
                $hours = $deliveryTime['hours'];
                $minutes = $deliveryTime['minutes'];
                $delayedStart = $this->_toSeconds($days, $hours, $minutes);

                $coupon = $config['coupon'];

                if (!isset($coupon[self::COUPON_CODE_USE_RULE])) {
                    $coupon[self::COUPON_CODE_USE_RULE] = false;
                }

                $object->addData(
                    array_merge(
                        [
                            'rule_id' => $this->getId(),
                            'email_template_id' => $config['email_template_id'],
                            'delayed_start' => $delayedStart,
                        ],
                        $coupon
                    )
                );

                $object->save();

                $savedIds[] = $object->getId();
            }
            $deleteCollection = $this->objectManager
                ->create(\Amasty\Followup\Model\Schedule::class)->getCollection()
                ->addFieldToFilter('rule_id', $this->getId())
                ->addFieldToFilter(
                    'schedule_id',
                    [
                        'nin' => $savedIds
                    ]
                );

            foreach ($deleteCollection as $delete) {
                $delete->delete();
            }

            $ruleProductAttributes = array_merge(
                $this->_getUsedAttributes($this->getConditionsSerialized()),
                $this->_getUsedAttributes($this->getActionsSerialized())
            );

            if (count($ruleProductAttributes)) {
                $this->getResource()->saveAttributes($this->getId(), $ruleProductAttributes);
            }
        }
    }

    /**
     * Return all product attributes used on serialized action or condition
     *
     * @param string $serializedString
     *
     * @return array
     */
    protected function _getUsedAttributes($serializedString)
    {
        $result = [];
        $pattern = '~s:46:"Magento\\\SalesRule\\\Model\\\Rule\\\Condition\\\Product";s:9:"attribute";s:\d+:"(.*?)"~s';
        $matches = [];

        if (preg_match_all($pattern, $serializedString, $matches)) {
            foreach ($matches[1] as $attributeCode) {
                $result[] = $attributeCode;
            }
        }

        return $result;
    }

    /**
     * @param string $days
     * @param string $hours
     * @param string $minutes
     *
     * @return int
     */
    protected function _toSeconds($days, $hours, $minutes)
    {
        return (int)$minutes * 60 + ((int)$hours * 60 * 60) + ((int)$days * 24 * 60 * 60);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return bool
     */
    public function validateConditions($quote)
    {
        /** @var \Magento\Quote\Model\Quote\Address $address */
        foreach ($quote->getAllAddresses() as $address) {
            $this->_initAddress($address, $quote);

            if (parent::validate($address)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $address
     * @param \Magento\Quote\Model\Quote $quote
     */
    protected function _initAddress($address, $quote)
    {
        $address->setData('total_qty', $quote->getData('items_qty'));
    }

    /**
     * @return array
     */
    public function getCancelEvents()
    {
        $cancelEvents = [];

        $cancelTypes = explode(",", $this->getCancelEventType());

        foreach ($cancelTypes as $cancelEventType) {
            if ($cancelEvents) {
                $cancelEvents[] = $this->eventCreator->create($cancelEventType, ['rule' => $this]);
            }
        }

        if ($this->isOrderRelated()) {
            $state = [
                SalesOrder::STATE_PROCESSING,
                SalesOrder::STATE_COMPLETE,
                SalesOrder::STATE_CLOSED,
                SalesOrder::STATE_CANCELED
            ];

            $orderStatus = $this->objectManager
                ->get(\Magento\Sales\Model\ResourceModel\Order\Status\Collection::class)
                ->joinStates()
                ->addFieldToFilter('state_table.state', ['in' => $state])
                ->addFieldToFilter('state_table.is_default', ['eq' => 1]);

            /** @var \Magento\Sales\Model\Order\Status $status */
            foreach ($orderStatus as $status) {
                $eventKey = $this->getOrderCancelEventKey($status);

                if (in_array($eventKey, $cancelTypes)) {
                    $cancelEvents[] = $this->eventCreator->create(
                        self::TYPE_CANCEL_ORDER_STATUS,
                        ['rule' => $this, 'status' => $status]
                    );
                }
            }
        }

        return $cancelEvents;
    }

    /**
     * @param \Magento\Sales\Model\Order\Status $status
     *
     * @return string
     */
    public function getOrderCancelEventKey($status)
    {
        return self::TYPE_CANCEL_ORDER_STATUS . $status->getStatus();
    }
}
