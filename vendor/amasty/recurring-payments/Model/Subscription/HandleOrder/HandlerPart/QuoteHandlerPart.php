<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPart;

use Amasty\RecurringPayments\Api\Generators\QuoteGeneratorInterface;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandleOrderContext;
use Amasty\RecurringPayments\Model\Subscription\HandleOrder\HandlerPartInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class QuoteHandlerPart implements HandlerPartInterface
{
    /**
     * @var QuoteGeneratorInterface
     */
    private $quoteGenerator;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(QuoteGeneratorInterface $quoteGenerator, OrderRepositoryInterface $orderRepository)
    {
        $this->quoteGenerator = $quoteGenerator;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param HandleOrderContext $context
     * @return bool
     */
    public function handlePartial(HandleOrderContext $context): bool
    {
        $order = $this->orderRepository->get($context->getSubscription()->getOrderId());
        $shippingAddress = $order->getIsVirtual() ? null : $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();

        $newQuote = $this->quoteGenerator->generate(
            $context->getSubscription(),
            $shippingAddress,
            $billingAddress
        );
        $context->setQuote($newQuote);

        return true;
    }

    /**
     * @param HandleOrderContext $context
     * @throws \InvalidArgumentException
     */
    public function validate(HandleOrderContext $context): void
    {
        $subscription = $context->getSubscription();

        if (!$subscription) {
            throw new \InvalidArgumentException('No subscription in context');
        }
    }
}
