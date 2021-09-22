<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Plugin\Model\Order;

use Magento\Sales\Api\Data\{CreditmemoInterface, OrderPaymentInterface};

/**
 * Class PaymentPlugin
 */
class PaymentPlugin
{
    /**
     * @param OrderPaymentInterface $subject
     * @param CreditmemoInterface $creditmemo
     *
     * @return array
     */
    public function beforeRefund(OrderPaymentInterface $subject, CreditmemoInterface $creditmemo): array
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $creditmemo->getOrder();

        if ($order) {
            $grandTotal = $creditmemo->getBaseGrandTotal();
            $paymentAuthorized = $order->getData('payment')->getBaseAmountAuthorized();

            if ($paymentAuthorized && $grandTotal > $paymentAuthorized) {
                $creditmemo->setBaseGrandTotal((float)$paymentAuthorized);
            }
        }

        return [$creditmemo];
    }
}
