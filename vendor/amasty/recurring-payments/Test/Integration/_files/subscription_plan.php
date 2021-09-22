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

/** @var SubscriptionPlanInterface $subscriptionPlanDaily */
$subscriptionPlanDaily = Bootstrap::getObjectManager()->create(SubscriptionPlanInterface::class);
$subscriptionPlanDaily->setPlanId(77);
$subscriptionPlanDaily->setName('Subscription Plan DAILY');
$subscriptionPlanDaily->setFrequency(1);
$subscriptionPlanDaily->setFrequencyUnit(BillingFrequencyUnit::DAY);
$subscriptionPlanDaily->setEnableTrial(0);
$subscriptionPlanDaily->setEnableInitialFee(0);
$subscriptionPlanDaily->setEnableDiscount(0);
$subscriptionPlanDaily->setStatus(PlanStatus::ACTIVE);
$subscriptionPlanDaily->isObjectNew(true);

$subscriptionPlanMonthly = clone $subscriptionPlanDaily;
$subscriptionPlanMonthly->setPlanId(88);
$subscriptionPlanMonthly->isObjectNew(true);


/** @var SubscriptionPlanRepositoryInterface $subscriptionPlanRepository */
$subscriptionPlanRepository = Bootstrap::getObjectManager()->get(SubscriptionPlanRepositoryInterface::class);
$subscriptionPlanRepository->save($subscriptionPlanDaily);
$subscriptionPlanRepository->save($subscriptionPlanMonthly);
