<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Controller\Customer;

use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\Subscription\Operation\SubscriptionCancelOperation;
use Amasty\RecurringPayments\Model\SubscriptionManagement;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Cancel Subscription Customer Controller
 *
 * @TODO: no errors are handled now, always send customer subscriptons' list
 */
class CancelSubscription extends Action
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var SubscriptionManagement
     */
    private $subscriptionManagement;

    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var SubscriptionCancelOperation
     */
    private $subscriptionCancelOperation;

    public function __construct(
        Context $context,
        Session $session,
        SubscriptionManagement $subscriptionManagement,
        SubscriptionCancelOperation $subscriptionCancelOperation,
        RepositoryInterface $subscriptionRepository
    ) {
        parent::__construct($context);
        $this->session = $session;
        $this->subscriptionManagement = $subscriptionManagement;
        $this->subscriptionCancelOperation = $subscriptionCancelOperation;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $subscriptionId = $this->getRequest()->getParam('subscription_id');
        try {
            $subscription = $this->subscriptionRepository->getBySubscriptionId($subscriptionId);
        } catch (NoSuchEntityException $e) {
            $subscription = null;
        }

        $customerId = $this->session->getCustomerId();
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
