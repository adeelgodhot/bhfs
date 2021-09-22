<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Api\SubscriptionPlanRepositoryInterface;
use Amasty\RecurringPayments\Model\Config\Source\BillingFrequencyUnit;
use Amasty\RecurringPayments\Model\Config\Source\PlanStatus;
use Magento\TestFramework\Helper\Bootstrap;

/** @var SubscriptionPlanRepositoryInterface $subscriptionPlanRepository */
$subscriptionPlanRepository = Bootstrap::getObjectManager()->get(SubscriptionPlanRepositoryInterface::class);
try {
    $subscriptionPlanRepository->deleteById(77);
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
}

try {
    $subscriptionPlanRepository->deleteById(88);
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
}
