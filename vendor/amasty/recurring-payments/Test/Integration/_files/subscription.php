<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


include __DIR__ . '/order.php';
include __DIR__ . '/customer.php';

use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\Sales\Model\Order $order */
/** @var \Magento\Catalog\Model\Product $product */

/** @var SubscriptionInterface $subscription */
$subscription = Bootstrap::getObjectManager()->create(SubscriptionInterface::class);
$subscription->setSubscriptionId('subscription_test');
$subscription->setOrderId($order->getId());
$subscription->setCreatedAt((new \DateTime())->format('Y-m-d H:i:s'));
$subscription->setProductId($product->getId());
$subscription->setQty(1);
$subscription->setCustomerId(777);
$subscription->setPaymentMethod('checkmo');
$subscription->setAddressId(null);
$subscription->setProductOptions('');
$subscription->setStoreId($order->getStoreId());
$subscription->setShippingMethod($order->getShippingMethod());
$subscription->setBaseGrandTotal($order->getBaseGrandTotal());
$subscription->setStatus(SubscriptionInterface::STATUS_ACTIVE);
$subscription->setFrequency(1);
$subscription->setFrequencyUnit('day');
$subscription->setStartDate((new \DateTime())->format('Y-m-d H:i:s'));
$subscription->setCustomerEmail($order->getCustomerEmail());
$subscription->setBaseGrandTotalWithDiscount($order->getBaseGrandTotal());

/** @var RepositoryInterface $subscriptionRepository */
$subscriptionRepository = Bootstrap::getObjectManager()->get(RepositoryInterface::class);
$subscriptionRepository->save($subscription);
