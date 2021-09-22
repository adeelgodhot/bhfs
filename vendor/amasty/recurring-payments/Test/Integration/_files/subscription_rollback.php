<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */

include __DIR__ . '/order_rollback.php';
include __DIR__ . '/customer_rollback.php';
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

/** @var RepositoryInterface $subscriptionRepository */
$subscriptionRepository = Bootstrap::getObjectManager()->get(RepositoryInterface::class);
try {
    $subscription = $subscriptionRepository->getBySubscriptionId('subscription_test');
    $subscriptionRepository->delete($subscription);
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
}
