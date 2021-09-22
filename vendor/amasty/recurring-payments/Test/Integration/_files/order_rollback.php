<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;

require __DIR__ . '/subscription_product_rollback.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var OrderRepositoryInterface $orderRepository */
$orderRepository = $objectManager->create(OrderRepositoryInterface::class);
try {
    $order = $orderRepository->get(999999999);
    $orderRepository->delete($order);
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
}
