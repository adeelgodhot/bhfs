<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\Operation;

use Amasty\RecurringPayments\Api\Subscription\CancelProcessorInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Psr\Log\LoggerInterface;

/**
 * Full action "Cancel" for subscription by subscription object.
 */
class SubscriptionCancelOperation
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CancelProcessorInterface[]
     */
    private $cancelHandlers;

    /**
     * @var SaveCancelStatus
     */
    private $saveCancelStatus;

    public function __construct(
        LoggerInterface $logger,
        SaveCancelStatus $saveCancelStatus,
        array $cancelHandlers = []
    ) {
        $this->logger = $logger;
        $this->cancelHandlers = $cancelHandlers;
        $this->saveCancelStatus = $saveCancelStatus;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return bool
     */
    public function execute(SubscriptionInterface $subscription): bool
    {
        $subscriptionPayment = $subscription->getPaymentMethod();
        if (isset($this->cancelHandlers[$subscriptionPayment])) {
            try {
                $this->cancelHandlers[$subscriptionPayment]->process($subscription);
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());

                return false;
            }
        }

        $this->saveCancelStatus->execute($subscription);

        return true;
    }
}
