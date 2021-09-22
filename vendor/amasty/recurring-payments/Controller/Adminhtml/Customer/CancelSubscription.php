<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Controller\Adminhtml\Customer;

use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\Subscription\Operation\SubscriptionCancelOperation;
use Amasty\RecurringPayments\Model\SubscriptionManagement;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class CancelSubscription extends Action
{
    /**
     * @var SubscriptionManagement
     */
    private $subscriptionManagement;

    /**
     * @var SubscriptionCancelOperation
     */
    private $subscriptionCancelOperation;

    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;

    public function __construct(
        Context $context,
        SubscriptionManagement $subscriptionManagement,
        RepositoryInterface $subscriptionRepository,
        SubscriptionCancelOperation $subscriptionCancelOperation
    ) {
        parent::__construct($context);
        $this->subscriptionManagement = $subscriptionManagement;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionCancelOperation = $subscriptionCancelOperation;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $subscriptionId = $this->getRequest()->getParam('subscription_id');
        $customerId = $this->getRequest()->getParam('customer_id');

        try {
            $subscription = $this->subscriptionRepository->getBySubscriptionId($subscriptionId);
        } catch (NoSuchEntityException $e) {
            $subscription = null;
        }

        if ($subscription && $subscription->getCustomerId() == $customerId) {
            $this->subscriptionCancelOperation->execute($subscription);
        }

        $subscriptions = $this->subscriptionManagement->getSubscriptions((int)$customerId);

        /** @var Json $jsonResponse */
        $jsonResponse = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $jsonResponse->setData($subscriptions);

        return $jsonResponse;
    }
}
