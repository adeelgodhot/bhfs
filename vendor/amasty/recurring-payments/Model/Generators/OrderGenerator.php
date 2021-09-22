<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Generators;

use Amasty\RecurringPayments\Api\Generators\OrderGeneratorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\PaymentFactory;
use Magento\Quote\Model\QuoteValidator;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderGenerator implements OrderGeneratorInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var QuoteValidator
     */
    private $quoteValidator;

    /**
     * @var OrderGenerator\QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    public function __construct(
        QuoteValidator $quoteValidator,
        OrderRepositoryInterface $orderRepository,
        PaymentFactory $paymentFactory,
        OrderGenerator\QuoteManagement $quoteManagement
    ) {
        $this->orderRepository = $orderRepository;
        $this->quoteValidator = $quoteValidator;
        $this->quoteManagement = $quoteManagement;
        $this->paymentFactory = $paymentFactory;
    }

    public function generate(
        CartInterface $quote,
        string $paymentMethod,
        string $currency
    ): OrderInterface {
        if ($paymentMethod) {
            $payment = $this->paymentFactory->create();
            $payment->setMethod($paymentMethod);
            $payment->setChecks([
                AbstractMethod::CHECK_USE_CHECKOUT,
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX
            ]);
            $quote->getPayment()->setQuote($quote);

            $data = $payment->getData();
            $quote->getPayment()->importData($data);
        } else {
            $quote->collectTotals();
        }

        $this->quoteValidator->validateBeforeSubmit($quote);
        $order = $this->quoteManagement->submitCustomQuote($quote);
        $order->setStatus(Order::STATE_PROCESSING);
        $order->setState(Order::STATE_PROCESSING);
        $order->setOrderCurrencyCode($currency);

        if (null == $order) {
            throw new LocalizedException(
                __('A server error stopped your order from being placed. Please try to place your order again.')
            );
        }

        $order->setTotalPaid($order->getGrandTotal());
        $order->setBaseTotalPaid($order->getBaseGrandTotal());

        $this->orderRepository->save($order);

        return $order;
    }
}
