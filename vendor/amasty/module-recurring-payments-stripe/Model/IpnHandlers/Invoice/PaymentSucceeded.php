<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\IpnHandlers\Invoice;

use Amasty\RecurringPayments\Api\Generators\RecurringTransactionGeneratorInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface as SubscriptionRepositoryInterface;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Config\Source\Status;
use Amasty\RecurringPayments\Model\Date;
use Amasty\RecurringPayments\Model\Subscription\EmailNotifier;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\CompositeHandler;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\CompositeHandlerFactory;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContextFactory;
use Amasty\RecurringStripe\Model\Adapter;

class PaymentSucceeded extends AbstractInvoice
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var SubscriptionRepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var CompositeHandlerFactory
     */
    private $compositeHandlerFactory;

    /**
     * @var HandleOrderContextFactory
     */
    private $handleOrderContextFactory;

    public function __construct(
        Config $config,
        Adapter $adapter,
        EmailNotifier $emailNotifier,
        RepositoryInterface $subscriptionRepository,
        Date $date,
        RecurringTransactionGeneratorInterface $recurringTransactionGenerator,
        CompositeHandlerFactory $compositeHandlerFactory,
        HandleOrderContextFactory $handleOrderContextFactory
    ) {
        parent::__construct(
            $config,
            $emailNotifier,
            $subscriptionRepository,
            $date,
            $recurringTransactionGenerator
        );
        $this->adapter = $adapter;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->compositeHandlerFactory = $compositeHandlerFactory;
        $this->handleOrderContextFactory = $handleOrderContextFactory;
    }

    /**
     * @param \Stripe\Event $event
     */
    public function process(\Stripe\Event $event)
    {
        $this->saveTransactionLog($event, Status::SUCCESS);
        /** @var \Stripe\Invoice $object */
        $object = $event->data->object;
        if (!$object->amount_paid || !$object->charge) {
            return;
        }

        $subscription = $this->getSubscription($event);
        if (!$subscription) {
            return;
        }

        $transactionId = $object->charge ?? $object->id;

        /** @var HandleOrderContext $handleOrderContext */
        $handleOrderContext = $this->handleOrderContextFactory->create();

        $handleOrderContext->setSubscription($subscription);
        $handleOrderContext->setTransactionId($transactionId);

        /** @var CompositeHandler $compositeHandler */
        $compositeHandler = $this->compositeHandlerFactory->create();

        $compositeHandler->handle($handleOrderContext);

        if ($subscription->getRemainingDiscountCycles() > 0) {
            $subscription->setRemainingDiscountCycles(
                $subscription->getRemainingDiscountCycles() - 1
            );
            $this->subscriptionRepository->save($subscription);

            if ($subscription->getRemainingDiscountCycles() === 0) {
                try {
                    $this->adapter->discountDelete($subscription->getSubscriptionId());
                } catch (\Exception $e) {
                    ;// do nothing
                }
            }
        }
    }
}
