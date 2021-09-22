<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Controller\Adminhtml\Rule;

use Amasty\Followup\Model\RuleFactory;
use Amasty\Followup\Model\ScheduleFactory;
use Amasty\Followup\Model\SalesRuleFactory;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\OrderRepository;

class TestEmail extends \Amasty\Followup\Controller\Adminhtml\Rule
{
    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        SalesRuleFactory $salesRuleFactory,
        RuleFactory $ruleFactory,
        ScheduleFactory $scheduleFactory,
        CustomerRepositoryInterface $customerRepository,
        OrderRepository $orderRepository,
        QuoteRepository $quoteRepository,
        JsonFactory $resultJsonFactory
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->scheduleFactory = $scheduleFactory;
        $this->customerRepository = $customerRepository;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct(
            $context,
            $coreRegistry,
            $salesRuleFactory
        );
    }

    public function execute()
    {
        /** @var Json $result */
        $resultJson = $this->resultJsonFactory->create();
        $ruleId = $this->getRequest()->getParam('rule_id');
        $rule = $this->ruleFactory->create()->load($ruleId);

        if ($rule->isOrderRelated()) {
            $this->testOrderRule($rule);
        } else {
            $this->testCustomerRule($rule);
        }

        $messages = $this->getMessageManager()->getMessages(true);

        return $resultJson->setData(
            [
                'error' => $messages->getCount() > 0,
                'errorMsg' => $messages->getCount() > 0 ? $messages->getLastAddedMessage()->getText() : null
            ]
        );
    }

    /**
     * @param \Amasty\Followup\Model\Rule $rule
     *
     * @throws \Magento\Framework\Exception\MailException
     */
    protected function testCustomerRule($rule)
    {
        $customerId = $this->getRequest()->getParam('id');
        $customer = $this->customerRepository->getById($customerId);

        if ($rule->getId() && $customer->getId()) {
            $schedule = $this->scheduleFactory->create();
            $event = $rule->getStartEvent();
            $historyItems = $schedule->createCustomerHistory($rule, $event, $customer);

            foreach ($historyItems as $history) {
                $history->processItem($rule, null, true);
            }
        }
    }

    /**
     * @param \Amasty\Followup\Model\Rule $rule
     *
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function testOrderRule($rule)
    {
        $orderId = $this->getRequest()->getParam('id');
        $order = $this->orderRepository->get($orderId);
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $customer = $quote->getCustomer();

        if ($rule->getId() && $order->getId() && $quote->getId()) {
            $schedule = $this->scheduleFactory->create();
            $event = $rule->getStartEvent();
            $historyItems = $schedule->createOrderHistory($rule, $event, $order, $quote, $customer);

            foreach ($historyItems as $history) {
                $history->processItem($rule, null, true);
            }
        }
    }
}
