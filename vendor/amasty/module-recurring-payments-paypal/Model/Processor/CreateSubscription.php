<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\Processor;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Subscription\Scheduler\DateTimeInterval;
use Amasty\RecurringPaypal\Model\Api\Adapter;
use Amasty\RecurringPaypal\Model\Subscription\Confirmation\LinksPersistor;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;

class CreateSubscription extends AbstractProcessor
{
    const SECONDS_RESERVE = 60;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlManager;

    /**
     * @var LinksPersistor
     */
    private $linksPersistor;

    /**
     * @var DateTimeInterval
     */
    private $dateTimeInterval;

    public function __construct(
        Adapter $adapter,
        StoreManagerInterface $storeManager,
        Config $config,
        UrlInterface $urlManager,
        LinksPersistor $linksPersistor,
        DateTimeInterval $dateTimeInterval
    ) {
        parent::__construct($adapter);
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->urlManager = $urlManager;
        $this->linksPersistor = $linksPersistor;
        $this->dateTimeInterval = $dateTimeInterval;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param string $planId
     * @param OrderInterface $order
     * @return SubscriptionInterface
     */
    public function execute(
        SubscriptionInterface $subscription,
        string $planId,
        OrderInterface $order
    ) {
        $params = [
            'plan_id'             => $planId,
            'subscriber'          => [
                'name'          => [
                    'given_name' => $order->getCustomerFirstname(),
                    'surname'    => $order->getCustomerLastname(),
                ],
                'email_address' => $order->getCustomerEmail(),
            ],
            'application_context' => [
                'brand_name'          => $this->storeManager->getStore($order->getStoreId())->getName(),
                'locale'              => $this->getStoreLocale((int)$order->getStoreId()),
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                'user_action'         => 'SUBSCRIBE_NOW',
                'payment_method'      => [
                    'payer_selected'  => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
                'return_url'          => $this->getReturnUrl(),
                'cancel_url'          => $this->getCancelUrl(),
            ],
        ];

        $trialDays = $subscription->getTrialDays();
        if ($trialDays) {
            $startTimestamp = strtotime($subscription->getStartDate());
            if ($startTimestamp > time() + self::SECONDS_RESERVE) {
                $params['start_time'] = date(DATE_ATOM, $startTimestamp);
            }
        } else {
            $nextPaymentDate = $this->dateTimeInterval->getNextBillingDate(
                $subscription->getStartDate(),
                $subscription->getFrequency(),
                $subscription->getFrequencyUnit()
            );
            $params['start_time'] = date(DATE_ATOM, strtotime($nextPaymentDate));
        }

        $subscriptionData = $this->adapter->createSubscription($params);

        $link = $this->getApprovalLink($subscriptionData);

        if ($link) {
            $this->linksPersistor->push($link);
        }

        return $subscriptionData['id'];
    }

    /**
     * @return string
     */
    private function getCancelUrl(): string
    {
        return $this->urlManager->getUrl('checkout/onepage/success');
    }

    /**
     * @return string
     */
    private function getReturnUrl(): string
    {
        return $this->urlManager->getUrl('amasty_recurring/customer/subscriptions');
    }

    /**
     * @param array $subscriptionData
     * @return string
     */
    public function getApprovalLink(array $subscriptionData): string
    {
        foreach ($subscriptionData['links'] as $link) {
            if ($link['rel'] == 'approve') {
                return $link['href'];
            }
        }

        return '';
    }

    /**
     * @param int $storeId
     * @return string
     */
    private function getStoreLocale(int $storeId): string
    {
        return str_replace('_', '-', $this->config->getStoreLocale((string)$storeId));
    }
}
