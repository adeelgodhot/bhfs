<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Plugin\Gateway\Command;

use Amasty\Stripe\Gateway\Command\CaptureStrategyCommand;
use Magento\Sales\Api\OrderRepositoryInterface;

class CaptureStrategyCommandPlugin
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param CaptureStrategyCommand $subject
     * @param array $commandSubject
     *
     * @return array
     */
    public function beforeExecute(CaptureStrategyCommand $subject, array $commandSubject): array
    {
        /** @var \Magento\Payment\Gateway\Data\PaymentDataObject $payment */
        $payment = $commandSubject['payment'];
        /** @var \Magento\Sales\Model\Order $order */
        $orderId = $payment->getOrder()->getId();

        if ($orderId) {
            $order = $this->orderRepository->get($orderId);
            $commandSubject['amount'] = $order->getData('payment')->getBaseAmountAuthorized();
        }

        return [$commandSubject];
    }
}
