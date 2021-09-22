<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\Processors;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\DateTime\DateTimeComparer;
use Amasty\RecurringPayments\Model\Subscription\Mapper\BillingFrequencyLabelMapper;
use Amasty\RecurringPayments\Model\Subscription\Scheduler\DateTimeInterval;
use Amasty\RecurringStripe\Model\Adapter;
use Amasty\Stripe\Api\CustomerRepositoryInterface;
use Amasty\Stripe\Model\StripeAccountManagement;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Api\Data\OrderInterface;

class CreateSubscription extends AbstractProcessor
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CreateCoupon
     */
    private $createCoupon;

    /**
     * @var StripeAccountManagement
     */
    private $accountManager;

    /**
     * @var DateTimeInterval
     */
    private $dateTimeInterval;

    /**
     * @var DateTimeComparer
     */
    private $dateTimeComparer;

    /**
     * @var BillingFrequencyLabelMapper
     */
    private $billingFrequencyLabelMapper;

    public function __construct(
        Adapter $adapter,
        CustomerRepositoryInterface $customerRepository,
        CreateCoupon $createCoupon,
        StripeAccountManagement $accountManager,
        DateTimeInterval $dateTimeInterval,
        DateTimeComparer $dateTimeComparer,
        BillingFrequencyLabelMapper $billingFrequencyLabelMapper
    ) {
        parent::__construct($adapter);
        $this->customerRepository = $customerRepository;
        $this->createCoupon = $createCoupon;
        $this->accountManager = $accountManager;
        $this->dateTimeInterval = $dateTimeInterval;
        $this->dateTimeComparer = $dateTimeComparer;
        $this->billingFrequencyLabelMapper = $billingFrequencyLabelMapper;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param CartItemInterface $item
     * @param OrderInterface $order
     * @param \Stripe\ApiResource $stripePlanObject
     * @return string
     */
    public function execute(
        SubscriptionInterface $subscription,
        CartItemInterface $item,
        OrderInterface $order,
        \Stripe\ApiResource $stripePlanObject
    ) {
        $coupon = null;
        $trialDays = $subscription->getTrialDays();
        $paymentCard = $this->getPaymentCard($order);

        $discountAmount = $subscription->getBaseDiscountAmount();

        if ($discountAmount) {
            $coupon = $this->createCoupon->execute(
                $item,
                (float)$subscription->getBaseGrandTotal() - (float)$subscription->getBaseGrandTotalWithDiscount()
            );
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $item->getQuote();

        /** @var \Amasty\Stripe\Model\Customer $customer */
        $customer = $this->customerRepository->getStripeCustomer(
            $quote->getCustomerId(),
            $this->adapter->getAccountId()
        );

        $frequency = $subscription->getFrequency();
        $frequencyUnit = $subscription->getFrequencyUnit();

        $params = [
            'customer' => $customer->getStripeCustomerId(),
            'coupon' => $coupon ? $coupon->id : null,
            'items' => [
                [
                    'plan' => $stripePlanObject->id,
                    // we sent base grand total on plan creation step.
                    // and we need qty = 1 for correct invoicing in stripe
                    'quantity' => 1
                ]
            ],
            'default_payment_method' => empty($paymentCard) ? null : $paymentCard,
            'metadata' => $this->prepareItemData($item, $order, $subscription),
            'expand' => ['latest_invoice.payment_intent'],
            'payment_behavior' => 'allow_incomplete',
            'proration_behavior' => 'none',
        ];

        $startDateIsToday = $this->dateTimeComparer->compareDates(
            $subscription->getStartDate(),
            date('Y-m-d')
        );

        if ($trialDays) {
            $params['trial_end'] = strtotime($this->dateTimeInterval->getStartDateAfterTrial(
                $subscription->getStartDate(),
                $trialDays
            ));
        } else {
            $nextBillingDate = $this->dateTimeInterval->getNextBillingDate(
                $subscription->getStartDate(),
                $frequency,
                $frequencyUnit
            );
            $nextBillingDate = strtotime($nextBillingDate);

            if ($startDateIsToday) {
                $params['billing_cycle_anchor'] = $nextBillingDate;
            } else {
                $params['trial_end'] = $nextBillingDate;
            }
        }

        if ($subscription->getEndDate()) {
            $cancelAtDate = $this->dateTimeInterval->getNextBillingDate(
                $subscription->getEndDate(),
                $frequency,
                $frequencyUnit
            );
            $params['cancel_at'] = strtotime($cancelAtDate);
        }

        /** @var \Stripe\Subscription $stripeSubscription */
        $stripeSubscription = $this->adapter->subscriptionCreate($params);

        return $stripeSubscription->id;
    }

    /**
     * @param QuoteItem $item
     * @param OrderInterface $order
     * @param SubscriptionInterface $subscription
     * @return array
     */
    private function prepareItemData(QuoteItem $item, OrderInterface $order, SubscriptionInterface $subscription): array
    {
        $delivery = $this->billingFrequencyLabelMapper->getLabel(
            $subscription->getFrequency(),
            $subscription->getFrequencyUnit()
        );

        return [
            'increment_id' => $order->getIncrementId(),
            'delivery' => $delivery,
        ];
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    private function getPaymentCard(OrderInterface $order): string
    {
        $paymentAdditional = $order->getData('payment')->getAdditionalInformation();
        $paymentMethod = $paymentAdditional['payment_method'];
        $paymentMethod = explode(':', $paymentMethod);
        $paymentMethod = $this->accountManager->process($paymentMethod, 'save_for_recurring');

        return $paymentMethod->id;
    }
}
