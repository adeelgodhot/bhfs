<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPart;

use Amasty\RecurringPayments\Api\Generators\OrderGeneratorInterface;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderHandlerPart implements HandlerPartInterface
{
    /**
     * @var OrderGeneratorInterface
     */
    private $orderGenerator;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(OrderGeneratorInterface $orderGenerator, OrderRepositoryInterface $orderRepository)
    {

        $this->orderGenerator = $orderGenerator;
        $this->orderRepository = $orderRepository;
    }
    /**
     * @param HandleOrderContext $context
     * @return bool
     */
    public function handlePartial(HandleOrderContext $context): bool
    {
        $subscription = $context->getSubscription();
        $quote = $context->getQuote();
        $order = $this->orderRepository->get($subscription->getOrderId());

        /** @var Order $newOrder */
        $newOrder = $this->orderGenerator->generate(
            $quote,
            $subscription->getPaymentMethod(),
            $order->getOrderCurrencyCode()
        );

        $context->setOrder($newOrder);

        return true;
    }

    /**
     * @param HandleOrderContext $context
     * @throws \InvalidArgumentException
     */
    public function validate(HandleOrderContext $context): void
    {
        if (!$context->getSubscription()) {
            throw new \InvalidArgumentException('No subscription in context');
        }

        if (!$context->getQuote()) {
            throw new \InvalidArgumentException('No quote in context');
        }
    }
}
