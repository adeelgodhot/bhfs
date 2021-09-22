<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Event;

use Amasty\Followup\Model\Formatmanager;
use Amasty\Followup\Model\History;
use Amasty\Followup\Model\ResourceModel\History\Collection;
use Amasty\Followup\Model\Urlmanager;
use Magento\Email\Model\Template as EmailTemplate;
use Magento\Framework\Filter\Template;
use Amasty\Followup\Model;

class Basic extends \Magento\Framework\DataObject
{
    protected $_rule;

    const LAST_EXECUTED_PATH = 'amfollowup/common/last_executed';

    protected $_actualGap = 3600;

    protected $_lastExecuted = null;

    protected $_currentExecution = null;

    protected $_collections = [];

    protected $storeManager;

    protected $_helper;

    protected $_statusHistory;

    protected $_dateTime;

    protected $_date;

    const STATUS_KEY_CREATE_ORDER = 'new';

    const STATUS_KEY_INVOICE_ORDER = 'invoice';

    const STATUS_KEY_CANCEL_ORDER = 'cancel';

    const STATUS_KEY_COMPLETE_ORDER = 'complete';

    const STATUS_KEY_SHIP_ORDER = 'ship';

    const LAYOUT_CONSTRUCTION = 'layout';

    const CROSSEL_LAYOUT_HANDLE = 'amfollowup_email_crosssell';

    /**
     * Resource model of config data
     *
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    protected $_configInterface;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_status;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $collectionCustomerFactory;

    /**
     * @var \Amasty\Followup\Model\Factories\SegmentFactory
     */
    protected $segmentFactory;

    /**
     * @var \Magento\Framework\FlagFactory
     */
    protected $flagManagerFactory;

    /**
     * @var \Magento\Framework\Flag
     */
    private $flagData;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Followup\Model\Rule $rule,
        \Amasty\Followup\Helper\Data $helper,
        \Magento\Sales\Model\Order\Status\History $statusHistory,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionCustomerFactory,
        \Amasty\Followup\Model\Factories\SegmentFactory $segmentFactory,
        \Magento\Framework\FlagFactory $flagManagerFactory,
        $status = null,
        array $data = []
    ) {
        $this->_data = $data;
        $this->storeManager = $storeManager;
        $this->_rule = $rule;
        $this->_helper = $helper;
        $this->_statusHistory = $statusHistory;
        $this->_configInterface = $configInterface;
        $this->_dateTime = $dateTime;
        $this->_date = $date;
        $this->_objectManager = $objectManager;
        $this->_status = $status;
        $this->order = $order;
        $this->collectionCustomerFactory = $collectionCustomerFactory;
        $this->segmentFactory = $segmentFactory;
        $this->flagManagerFactory = $flagManagerFactory;
        parent::__construct($data);
    }

    /**
     * @param int $storeId
     *
     * @return bool
     */
    protected function _validateStore($storeId)
    {
        $storesIds = $this->_rule->getStores();
        $arrStores = explode(',', $storesIds);

        return empty($storesIds) || in_array($storeId, $arrStores);
    }

    /**
     * @param string $customerEmail
     *
     * @return bool
     */
    protected function _validateCustomer($customerEmail)
    {
        $ret = true;
        $segments = $this->_rule->getSegments();

        if (($this->_helper->isModuleOutputEnabled(\Amasty\Followup\Helper\Data::AMASTY_SEGMENT_MODULE_DEPEND_NAMESPACE)
            && !empty($segments))
        ) {
            $arrSegments = explode(',', $segments);

            /** @var \Amasty\Segments\Model\SegmentRepository $segmentsRepository */
            $segmentsRepository = $this->segmentFactory->getSegmentRepository();

            foreach ($arrSegments as $segment) {
                $model = $segmentsRepository->get($segment);
                $salesRule = $model->getSalesRule();

                if ($model && $model->getId()) {
                    $customer = $this
                        ->collectionCustomerFactory
                        ->create()
                        ->addFieldToFilter('email', ['eq' => $customerEmail])
                        ->getFirstItem();

                    if ($customer->getId()) {
                        $validateByIndex = $salesRule->validateByIndex(
                            $this->segmentFactory->getValidationField(),
                            $arrSegments,
                            $customer->getId()
                        );

                        if (!$validateByIndex && !$salesRule->validate($customer)) {
                            return false;
                        }
                    }
                }
            }
        }
        return $ret;
    }

    /**
     * @param int $customerGroupId
     *
     * @return bool
     */
    protected function _validateCustomerGroup($customerGroupId)
    {
        $customerGroupsIds = $this->_rule->getCustGroups();
        $arrCustomerGroups = explode(',', $customerGroupsIds);

        return empty($customerGroupsIds) || in_array($customerGroupId, $arrCustomerGroups);
    }

    /**
     * @param int $storeId
     * @param string $customerEmail
     * @param int $customerGroupId
     *
     * @return bool
     */
    protected function _validateBasic($storeId, $customerEmail, $customerGroupId)
    {
        return $this->_rule->getIsActive() == 1
            && $this->_validateStore($storeId)
            && $this->_validateCustomer($customerEmail)
            && $this->_validateCustomerGroup($customerGroupId);
    }

    /**
     * @param $object
     *
     * @return bool
     */
    public function validate($object)
    {
        return true;
    }

    /**
     * @return string
     */
    protected function _getCollectionKey()
    {
        return $this->_rule->getId() . "_" . $this->_rule->getStartEventType();
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getCollection()
    {
        if (!isset($this->_collections[$this->_getCollectionKey()])) {
            $this->setCollection();
        }

        return $this->_collections[$this->_getCollectionKey()];
    }

    public function setCollection()
    {
        $this->_collections[$this->_getCollectionKey()] = $this->_initCollection();
    }

    protected function _initCollection()
    {
        return null;
    }

    public function clear()
    {
        $this->_lastExecuted = null;
        $this->_currentExecution = null;
        $this->_collections = [];
    }

    /**
     * @return int
     */
    public function getLastExecuted()
    {
        if ($this->_lastExecuted === null) {
            $flag = $this->getFlag()->loadSelf();
            $this->_lastExecuted = (string)$flag->getFlagData();

            if (empty($this->_lastExecuted)) {
                $this->_lastExecuted = $this->_date->gmtTimestamp() - $this->_actualGap;
            }

            $flag->setFlagData($this->getCurrentExecution());
        }

        return $this->_lastExecuted;
    }

    /**
     * @return \Magento\Framework\Flag
     */
    public function getFlag()
    {
        if ($this->flagData === null) {
            $this->flagData = $this->flagManagerFactory->create(['data' => ['flag_code' => self::LAST_EXECUTED_PATH]]);
        }

        return $this->flagData;
    }

    /**
     * @return int|null
     */
    public function getCurrentExecution()
    {
        return $this->_currentExecution ? $this->_currentExecution : $this->_date->gmtTimestamp();
    }

    /**
     * @param \Amasty\Followup\Model\Schedule $schedule
     * @param History $history
     * @param array $vars
     *
     * @return array|bool
     * @throws \Magento\Framework\Exception\MailException
     */
    public function getEmail(Model\Schedule $schedule, History $history, $vars = [])
    {
        $templateId = $schedule->getEmailTemplateId();

        $ret = [
            'body' => '',
            'subject' => ''
        ];

        $storeId = $history->getStoreId();
        $urlManager = $this->_objectManager->create(Urlmanager::class)->init($history);
        $formatManager = $this->_objectManager->create(Formatmanager::class)->init($vars);

        $variables = array_merge(
            [
                'urlmanager' => $urlManager,
                'unsubscribeUrl' => $urlManager->unsubscribeUrl(),
                'formatmanager' => $formatManager,
                'dateOfBirth' => $formatManager->formatDate('customer', 'dob'),
                'registrationDate' => $formatManager->formatDate('customer', 'created_at'),
                'lastLoginDate' => $formatManager->formatDate('customer_log', 'login_at'),
                'lastLoginAt' => $formatManager->formatDate('customer_log', 'last_login_at'),
                'grandTotal' => $formatManager->formatPrice('order', 'grand_total'),
                'orderCreatedAt' => $formatManager->formatDate('order', 'created_at'),
                'paymentMethod' => $formatManager->getOrderPaymentMethodLabel(),
                'store' => $this->storeManager->getStore($storeId),
                'store_name' => $this->storeManager->getStore($storeId)->getName()
            ],
            $vars
        );

        $emailTemplate = $this->_objectManager
            ->create(EmailTemplate::class);
        $emailTemplate->setData('is_legacy', true);
        $emailTemplate->setDesignConfig(
            [
                'area' => 'frontend',
                'store' => $storeId
            ]
        );

        if (is_numeric($templateId)) {
            $emailTemplate->load($templateId);
        } else {
            $localeCode = $this->_helper->getScopeValue('general/locale/code', $storeId);
            $emailTemplate->loadDefault($templateId, $localeCode);
        }

        if (!$emailTemplate->getId()) {
            throw new \Magento\Framework\Exception\MailException(
                __('Invalid transactional email code: ' . $templateId)
            );
        }

        $ret['body'] = $emailTemplate->getProcessedTemplate($variables, true);
        $ret['subject'] = $emailTemplate->getProcessedTemplateSubject($variables);

        return $ret;
    }

    /**
     * @param string $templateText
     *
     * @return bool
     */
    protected function checkCrossellLayoutExist($templateText)
    {
        if (preg_match_all(Template::CONSTRUCTION_PATTERN, $templateText, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {

                if ($construction[1] == self::LAYOUT_CONSTRUCTION) {
                    $tokenizer = new \Magento\Framework\Filter\Template\Tokenizer\Parameter();
                    $tokenizer->setString($construction[2]);
                    $params = $tokenizer->tokenize();

                    if (is_array($params)
                        && array_key_exists('handle', $params)
                        && ($params['handle'] == self::CROSSEL_LAYOUT_HANDLE)
                    ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param int $orderId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function crossellLayoutValid($orderId)
    {
        $order = $this->order->load($orderId);
        $isValid = false;

        if ($order->getId()) {
            foreach ($order->getItems() as $item) {
                $product = $item->getProduct();

                if (count($product->getCrossSellProducts()) > 0) {
                    $isValid = true;
                    break;
                }
            }

            return $isValid;
        } else {
            throw new\Magento\Framework\Exception\MailException('Order not existed ID: ' . $orderId);
        }

        return true;
    }

    /**
     * @return void
     */
    public function cancelEventWishlist()
    {
        $frequencyUpdate = 60; // 1 hour

        $collection = $this->_objectManager
            ->create(Collection::class);

        $collection->getSelect()->joinLeft(
            ['wishlist' => $collection->getTable('wishlist')],
            'main_table.customer_id = wishlist.customer_id',
            []
        );

        $collection->getSelect()->where(
            "main_table.status = '"
            . History::STATUS_PENDING
            . "' and main_table.rule_id = " . $this->_rule->getId()
            . " and main_table.created_at > '"
            . $this->_dateTime->formatDate((int)$this->getCurrentExecution() - $frequencyUpdate) . "'"
            . " and main_table.history_id is not null"
        );

        if ($collection->getSize()) {
            foreach ($collection as $history) {
                $history->setStatus(History::STATUS_CANCEL);
                $history->save();
            }
        }
    }

    /**
     * @param $flagCode
     *
     * @return \Magento\Framework\Flag
     */
    protected function createFlag($flagCode)
    {
        return $this->flagManagerFactory->create(['data' => ['flag_code' => $flagCode]]);
    }
}
