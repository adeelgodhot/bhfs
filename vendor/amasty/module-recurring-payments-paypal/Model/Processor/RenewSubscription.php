<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\Processor;

use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPaypal\Model\Api\Adapter;
use Amasty\RecurringPaypal\Model\ConfigProvider;
use Amasty\RecurringPayments\Model\Subscription\EmailNotifier;
use Amasty\RecurringPaypal\Model\Subscription\Processors\Cancel;

class RenewSubscription
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var CreateSubscription
     */
    private $createSubscription;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var EmailNotifier
     */
    private $emailNotifier;

    /**
     * @var Cancel
     */
    private $cancelProcessor;

    public function __construct(
        Adapter $adapter,
        RepositoryInterface $subscriptionRepository,
        CreateSubscription $createSubscription,
        ConfigProvider $configProvider,
        EmailNotifier $emailNotifier,
        Cancel $cancelProcessor
    ) {
        $this->adapter = $adapter;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->createSubscription = $createSubscription;
        $this->configProvider = $configProvider;
        $this->emailNotifier = $emailNotifier;
        $this->cancelProcessor = $cancelProcessor;
    }

    /**
     * @param SubscriptionInterface $subscription
     */
    public function execute(SubscriptionInterface $subscription)
    {
        $oldSubscriptionDetails = $this->adapter->getSubscriptionDetails($subscription->getSubscriptionId());
        $oldPlanDetails = $this->adapter->getPlanDetails($oldSubscriptionDetails['plan_id']);
        $oldCycle = end($oldPlanDetails['billing_cycles']);

        $totalCycles = $oldCycle['total_cycles'];

        if ($totalCycles != CreatePlan::TOTAL_CYCLES_INFINITE) {
            $totalCycles -= $subscription->getCountDiscountCycles();
            if ($totalCycles <= 0) {
                return;
            }
        }

        $this->cancelProcessor->process($subscription->getSubscriptionId());

        $newCycles = [
            [
                'frequency'      => $oldCycle['frequency'],
                'tenure_type'    => $oldCycle['tenure_type'],
                'total_cycles'   => $totalCycles,
                'pricing_scheme' => [
                    'fixed_price' => [
                        'value'         => $subscription->getBaseGrandTotal(),
                        'currency_code' => $oldCycle['pricing_scheme']['fixed_price']['currency_code'],
                    ],
                ],
                'sequence'       => 1
            ]
        ];

        $planDetails = $this->adapter->createPlan([
            'product_id'          => $oldPlanDetails['product_id'],
            'name'                => $oldPlanDetails['name'],
            'billing_cycles'      => $newCycles,
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
            ],
        ]);

        $subscriptionParams = [
            'plan_id'    => $planDetails['id'],
            'start_time' => $this->getNextPaymentDate($newCycles[0]['frequency']),
            'subscriber' => [
                'name'          => $oldSubscriptionDetails['subscriber']['name'],
                'email_address' => $oldSubscriptionDetails['subscriber']['email_address']
            ]
        ];

        $subscriptionDetails = $this->adapter->createSubscription($subscriptionParams);

        $subscription = clone $subscription;
        $subscription
            ->setId(null)
            ->setCreatedAt(null)
            ->setBaseGrandTotalWithDiscount($subscription->getBaseGrandTotal())
            ->setBaseDiscountAmount(0)
            ->setTrialDays(0)
            ->setInitialFee(0)
            ->setRemainingDiscountCycles(null)
            ->setSubscriptionId($subscriptionDetails['id']);
        $this->subscriptionRepository->save($subscription);

        $approvalLink = $this->createSubscription->getApprovalLink($subscriptionDetails);
        $this->sendRenewalNotification($subscription, $approvalLink);
    }

    /**
     * @param array $frequency
     * @return string
     */
    protected function getNextPaymentDate(array $frequency): string
    {
        $dateFormat = sprintf(
            '+%d %s',
            (int)$frequency['interval_count'],
            strtolower($frequency['interval_unit'])
        );

        return date(DATE_ATOM, strtotime($dateFormat));
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param string $approvalLink
     */
    protected function sendRenewalNotification(SubscriptionInterface $subscription, string $approvalLink)
    {
        $this->emailNotifier->sendEmail(
            $subscription,
            $this->configProvider->getRenewalEmailTemplate((int)$subscription->getStoreId()),
            ['approval_link' => $approvalLink]
        );
    }
}
