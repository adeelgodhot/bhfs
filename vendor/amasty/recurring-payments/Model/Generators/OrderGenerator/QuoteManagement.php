<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Generators\OrderGenerator;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

class QuoteManagement extends \Magento\Quote\Model\QuoteManagement
{
    public function submitCustomQuote(CartInterface $quote): OrderInterface
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create();

        $addresses = [];

        if ($quote->isVirtual()) {
            $this->dataObjectHelper->mergeDataObjects(
                OrderInterface::class,
                $order,
                $this->quoteAddressToOrder->convert($quote->getBillingAddress(), [])
            );
        } else {
            $this->dataObjectHelper->mergeDataObjects(
                OrderInterface::class,
                $order,
                $this->quoteAddressToOrder->convert($quote->getShippingAddress(), [])
            );
            $shippingAddress = $this->quoteAddressToOrderAddress->convert(
                $quote->getShippingAddress(),
                [
                    'address_type' => 'shipping',
                    'email' => $quote->getCustomerEmail()
                ]
            );
            $shippingAddress->setData('quote_address_id', $quote->getShippingAddress()->getId());
            $addresses[] = $shippingAddress;
            $order->setShippingAddress($shippingAddress);
            $order->setShippingMethod($quote->getShippingAddress()->getShippingMethod());
        }

        if (!$quote->getCustomerIsGuest()) {
            if ($quote->getCustomerId()) {
                $this->_prepareCustomerQuote($quote);
                $this->customerManagement->validateAddresses($quote);
            }
            $this->customerManagement->populateCustomerInfo($quote);
        }

        $billingAddress = $this->quoteAddressToOrderAddress->convert(
            $quote->getBillingAddress(),
            [
                'address_type' => 'billing',
                'email' => $quote->getCustomerEmail()
            ]
        );
        $billingAddress->setData('quote_address_id', $quote->getBillingAddress()->getId());
        $addresses[] = $billingAddress;
        $order->setBillingAddress($billingAddress);
        $order->setAddresses($addresses);
        $order->setPayment($this->quotePaymentToOrderPayment->convert($quote->getPayment()));
        $order->setItems($this->resolveItems($quote));
        if ($quote->getCustomer()) {
            $order->setCustomerId($quote->getCustomer()->getId());
        }
        $order->setCustomerEmail($quote->getCustomerEmail());
        $order->setCustomerFirstname($quote->getCustomerFirstname());
        $order->setCustomerMiddlename($quote->getCustomerMiddlename());
        $order->setCustomerLastname($quote->getCustomerLastname());

        $quote->setIsActive(false);

        $this->quoteRepository->save($quote);

        return $order;
    }
}
