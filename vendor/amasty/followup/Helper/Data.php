<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Helper;

use Amasty\Followup\Model\Rule as Rule;
use Magento\Email\Model\ResourceModel\Template;
use Magento\Email\Model\Template as EmailTemplate;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection;

class Data extends AbstractHelper
{
    const CONFIG_PATH_GENERAL_BIRTHDAY_OFFSET = 'amfollowup/general/birthday_offset';
    const AMASTY_SEGMENT_MODULE_DEPEND_NAMESPACE = 'Amasty_Segments';
    const SECONDS_IN_DAY = 24 * 60 * 60;
    const SECONDS_IN_HOUR = 60 * 60;
    const SECONDS_IN_MINUTE = 60;

    protected $coreRegistry;
    protected $_objectManager;
    protected $_scopeConfig;

    /**
     * @var Template
     */
    private $templateResourceModel;

    /**
     * @var Template\CollectionFactory
     */
    private $templateCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Email\Model\ResourceModel\Template $templateResourceModel,
        \Magento\Framework\Registry $registry,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templateCollectionFactory
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context);
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->templateResourceModel = $templateResourceModel;
        $this->templateCollectionFactory = $templateCollectionFactory;
    }

    public function getEventTypes()
    {
        return
            [
                [
                    'label' => __('Order'),
                    'value' => [
                        ['label' => __('Created'), 'value' => Rule::TYPE_ORDER_NEW],
                        ['label' => __('Shipped'), 'value' => Rule::TYPE_ORDER_SHIP],
                        ['label' => __('Invoiced'), 'value' => Rule::TYPE_ORDER_INVOICE],
                        ['label' => __('Completed'), 'value' => Rule::TYPE_ORDER_COMPLETE],
                        ['label' => __('Cancelled'), 'value' => Rule::TYPE_ORDER_CANCEL],
                    ]
                ],
                [
                    'label' => __('Customer'),
                    'value' => [
                        ['label' => __('No Activity'), 'value' => Rule::TYPE_CUSTOMER_ACTIVITY],
                        ['label' => __('Changed Group'), 'value' => Rule::TYPE_CUSTOMER_GROUP],
                        ['label' => __('Subscribed to Newsletter'), 'value' => Rule::TYPE_CUSTOMER_SUBSCRIPTION],
                        ['label' => __('Birthday'), 'value' => Rule::TYPE_CUSTOMER_BIRTHDAY],
                        ['label' => __('Registration'), 'value' => Rule::TYPE_CUSTOMER_NEW],
                    ]
                ],
                [
                    'label' => __('Wishlist'),
                    'value' => [
                        ['label' => __('Product Added'), 'value' => Rule::TYPE_CUSTOMER_WISHLIST],
                        ['label' => __('Shared'), 'value' => Rule::TYPE_CUSTOMER_WISHLIST_SHARED],
                        ['label' => __('Wishlist on sale'), 'value' => Rule::TYPE_CUSTOMER_WISHLIST_SALE],
                        ['label' => __('Wishlist back in stock'), 'value' => Rule::TYPE_CUSTOMER_WISHLIST_BACK_INSTOCK],
                    ]
                ],
                [
                    'label' => __('Date'),
                    'value' => [
                        ['label' => __('Date'), 'value' => Rule::TYPE_CUSTOMER_DATE]
                    ]
                ]
            ];
    }

    public function getCancelTypes($useOrderEvents = false)
    {
        $otherEvents = [];

        if ($useOrderEvents) {
            $otherEvents = array_merge($this->getOrderCancelEvents(), $otherEvents);
        }

        return array_merge([
            Rule::TYPE_CANCEL_CUSTOMER_LOGGEDIN => __('Customer logged in'),
            Rule::TYPE_CANCEL_ORDER_COMPLETE => __('New Order Placed'),
            Rule::TYPE_CANCEL_CUSTOMER_CLICKLINK => __('Customer clicked on a link in the email '),
            Rule::TYPE_CANCEL_CUSTOMER_WISHLIST_SHARED => __('Customer wishlist shared'),
        ], $otherEvents);
    }

    public function getOrderCancelEvents()
    {
        $orderCancelEvents = [];
        $state = [
            SalesOrder::STATE_PROCESSING,
            SalesOrder::STATE_COMPLETE,
            SalesOrder::STATE_CLOSED,
            SalesOrder::STATE_CANCELED
        ];
        $orderStatusCollection = $this->_objectManager
            ->create(Collection::class)
            ->joinStates()
            ->addFieldToFilter('state_table.state', ['in' => $state])
            ->addFieldToFilter('state_table.is_default', ['eq' => 1]);

        foreach ($orderStatusCollection as $status) {
            $orderCancelEvents[$this->getOrderCancelEventKey($status)] = __('Order Becomes: %1', $status->getLabel());
        }

        return $orderCancelEvents;
    }

    public function getOrderCancelEventKey($status)
    {
        return Rule::TYPE_CANCEL_ORDER_STATUS . $status->getStatus();
    }

    public function getScopeValue($path, $scoreCode = null)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $scoreCode);
    }

    public function createTemplate($templateCode, $templateLabel)
    {
        $template = $this->_objectManager
            ->create(EmailTemplate::class);

        $template->setData('is_legacy', true);
        $template->setForcedArea($templateCode);
        $template->loadDefault($templateCode);
        $template->setData('orig_template_code', $templateCode);
        $template->setData('template_variables', \Zend_Json::encode($template->getVariablesOptionArray(true)));
        $template->setData('template_code', $templateLabel);
        $template->setTemplateType(EmailTemplate::TYPE_HTML);
        $template->setId(null);
        if (!$this->templateResourceModel->checkCodeUsage($template)) {
            $template->save();
        }
    }

    /**
     * @param $type
     * @return $this
     */
    public function getEmailTemplatesCollection($type)
    {
        $collection = $this->templateCollectionFactory->create()
            ->addFieldToFilter(
                "orig_template_code",
                [
                    'like' => "%amfollowup_" . $type . "%"
                ]
            );

        if ($type == "customer_wishlist") {
            $collection->addFieldToFilter(
                "orig_template_code",
                ['nlike' => "%amfollowup_" . $type . "_shared%"]
            )->addFieldToFilter(
                "orig_template_code",
                ['nlike' => "%amfollowup_" . $type . "_sale%"]
            )->addFieldToFilter(
                "orig_template_code",
                ['nlike' => "%amfollowup_" . $type . "_back_instock%"]
            );
        }

        $collection->load();

        return $collection;
    }

    public function getDays($timestamp)
    {
        return $timestamp > 0 && floor($timestamp / self::SECONDS_IN_DAY) ?
            floor($timestamp / self::SECONDS_IN_DAY) :
            null;
    }

    public function getHours($timestamp)
    {
        $days = $this->getDays($timestamp);
        $time = $timestamp - ($days * self::SECONDS_IN_DAY);

        return $time > 0 ?
            floor($time / self::SECONDS_IN_HOUR) :
            null;
    }

    public function getMinutes($timestamp)
    {
        $days = $this->getDays($timestamp);
        $hours = $this->getHours($timestamp);
        $time = $timestamp - ($days * self::SECONDS_IN_DAY) - ($hours * self::SECONDS_IN_HOUR);

        return $time > 0 ?
            floor($time / self::SECONDS_IN_MINUTE) :
            null;
    }
}
