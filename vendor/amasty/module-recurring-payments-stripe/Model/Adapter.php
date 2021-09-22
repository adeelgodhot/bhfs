<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model;

use Amasty\Stripe\Model\Adapter\StripeAdapter;

class Adapter extends StripeAdapter
{
    /**
     * @param array $params
     *
     * @return \Stripe\ApiResource
     */
    public function productCreate(array $params)
    {
        return \Stripe\Product::create($params);
    }

    /**
     * @param string $productId
     *
     * @return \Stripe\StripeObject
     */
    public function productRetrieve(string $productId)
    {
        return \Stripe\Product::retrieve($productId);
    }

    /**
     * @param array $params
     *
     * @return \Stripe\ApiResource
     */
    public function planCreate(array $params)
    {
        return \Stripe\Plan::create($params);
    }

    /**
     * @param string $planId
     *
     * @return \Stripe\StripeObject
     */
    public function planRetrieve(string $planId)
    {
        return \Stripe\Plan::retrieve($planId);
    }

    /**
     * @param array params
     *
     * @return \Stripe\StripeObject
     */
    public function subscriptionCreate(array $params)
    {
        return \Stripe\Subscription::create($params);
    }

    /**
     * @param string $subscriptionId
     *
     * @return \Stripe\StripeObject
     */
    public function subscriptionRetrieve(string $subscriptionId)
    {
        return \Stripe\Subscription::retrieve($subscriptionId);
    }

    /**
     * @param array params
     *
     * @return \Stripe\StripeObject
     */
    public function couponCreate(array $params)
    {
        return \Stripe\Coupon::create($params);
    }

    /**
     * @param string $url
     *
     * @return \Stripe\ApiResource
     */
    public function webhookCreate(string $url)
    {
        return \Stripe\WebhookEndpoint::create([
            'url' => $url,
            'enabled_events' => [
                'invoice.payment_succeeded',
                'invoice.payment_failed',
                'invoice.payment_action_required',
                'customer.subscription.created',
                'customer.subscription.trial_will_end',
                'customer.subscription.deleted'
            ]
        ]);
    }

    /**
     * @param string $payload
     * @param string $sigHeader
     * @param string $webhookSecret
     *
     * @return \Stripe\Event
     */
    public function eventRetrieve(string $payload, string $sigHeader, string $webhookSecret)
    {
        return \Stripe\Webhook::constructEvent(
            $payload,
            $sigHeader,
            $webhookSecret
        );
    }

    /**
     * @param array $params
     *
     * @return \Stripe\Collection
     */
    public function subscriptionList(array $params)
    {
        return \Stripe\Subscription::all($params);
    }

    /**
     * @param string $invoiceId
     *
     * @return \Stripe\StripeObject
     */
    public function invoiceRetrieve(string $invoiceId)
    {
        return \Stripe\Invoice::retrieve($invoiceId);
    }

    /**
     * @param array $params
     *
     * @return \Stripe\Collection
     */
    public function invoiceList(array $params)
    {
        return \Stripe\Invoice::all($params);
    }

    /**
     * @param array $params
     *
     * @return \Stripe\Invoice
     */
    public function upcomingInvoiceRetrieve(array $params)
    {
        return \Stripe\Invoice::upcoming($params);
    }

    /**
     * @param array $params
     *
     * @return \Stripe\Invoice
     */
    public function discountDelete(string $subscriptionId)
    {
        $subscription = $this->subscriptionRetrieve($subscriptionId);
        $subscription->deleteDiscount();
    }
}
