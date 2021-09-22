<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\WebHook\Handler\Payment\Sale;

use Amasty\RecurringPayments\Api\Data\TransactionInterface;
use Amasty\RecurringPayments\Api\Generators\RecurringTransactionGeneratorInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\Date;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\CompositeHandler;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\CompositeHandlerFactory;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContextFactory;
use Amasty\RecurringPaypal\Api\WebHook\HandlerInterface;
use Amasty\RecurringPaypal\Model\Processor\RenewSubscription;
use Amasty\RecurringPaypal\Model\Subscription\Cache as SubscriptionCache;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;

class Completed implements HandlerInterface
{
    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var Date
     */
    private $date;

    /**
     * @var RenewSubscription
     */
    private $renewSubscription;

    /**
     * @var SubscriptionCache
     */
    private $subscriptionCache;

    /**
     * @var CompositeHandlerFactory
     */
    private $compositeHandlerFactory;

    /**
     * @var HandleOrderContextFactory
     */
    private $handleOrderContextFactory;

    /**
     * @var RecurringTransactionGeneratorInterface
     */
    private $recurringTransactionGenerator;

    public function __construct(
        RepositoryInterface $subscriptionRepository,
        OrderRepositoryInterface $orderRepository,
        Date $date,
        RenewSubscription $renewSubscription,
        SubscriptionCache $subscriptionCache,
        CompositeHandlerFactory $compositeHandlerFactory,
        HandleOrderContextFactory $handleOrderContextFactory,
        RecurringTransactionGeneratorInterface $recurringTransactionGenerator
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->orderRepository = $orderRepository;
        $this->date = $date;
        $this->renewSubscription = $renewSubscription;
        $this->subscriptionCache = $subscriptionCache;
        $this->compositeHandlerFactory = $compositeHandlerFactory;
        $this->handleOrderContextFactory = $handleOrderContextFactory;
        $this->recurringTransactionGenerator = $recurringTransactionGenerator;
    }

    /**
     * @param array $payload
     */
    public function process(array $payload)
    {
        $payment = $payload['resource'];
        $subscriptionId = $payment['billing_agreement_id'];
        $transactionId = $payment['id'];

        try {
            $subscription = $this->subscriptionRepository->getBySubscriptionId($subscriptionId);
        } catch (NoSuchEntityException $e) {
            return;
        }

        /** @var HandleOrderContext $handleOrderContext */
        $handleOrderContext = $this->handleOrderContextFactory->create();

        $handleOrderContext->setSubscription($subscription);
        $handleOrderContext->setTransactionId($transactionId);
        $order = $this->orderRepository->get($subscription->getOrderId());

        $this->recurringTransactionGenerator->generate(
            (float)$payment['amount']['total'],
            $order->getIncrementId(),
            $payment['amount']['currency'],
            $payment['id'],
            TransactionInterface::STATUS_SUCCESS,
            $subscription->getSubscriptionId(),
            $this->date->convertFromUnix(strtotime($payment['create_time']))
        );

        /** @var CompositeHandler $compositeHandler */
        $compositeHandler = $this->compositeHandlerFactory->create();
        $compositeHandler->handle($handleOrderContext);

        if ($subscription->getRemainingDiscountCycles() > 0) {
            $subscription->setRemainingDiscountCycles(
                $subscription->getRemainingDiscountCycles() - 1
            );
            $this->subscriptionRepository->save($subscription);

            if ($subscription->getRemainingDiscountCycles() === 0) {
                $this->renewSubscription->execute($subscription);
            }
        }

        $this->subscriptionCache->clearSubscriptionData((string)$subscription->getSubscriptionId());
    }
}
