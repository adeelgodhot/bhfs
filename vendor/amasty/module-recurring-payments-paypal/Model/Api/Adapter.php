<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\Api;

use Amasty\RecurringPaypal\Model\ConfigProvider;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Handler\RestHandler;
use PayPal\Rest\ApiContext;
use PayPal\Transport\PayPalRestCall;

class Adapter
{
    protected $apiContext;

    public function __construct(
        ConfigProvider $config
    ) {
        list($id, $secret) = $config->getPaypalCredentials();

        if (!$id) {
            throw new \RuntimeException('Can\'t use Paypal API. Credentials are not specified');
        }

        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($id, $secret)
        );
        $this->apiContext->setConfig(['mode' => $config->getPaymentMode()]);
    }

    public function call(string $url, string $method, array $payLoad = []): array
    {
        $payLoad = json_encode($payLoad);
        $restCall = new PayPalRestCall($this->apiContext);

        $response = $restCall->execute([RestHandler::class], $url, $method, $payLoad);

        return $response ? json_decode($response, true) : [];
    }

    public function createProduct(array $data): array
    {
        return $this->call('/v1/catalogs/products', 'POST', $data);
    }

    public function getProductDetails(string $id): array
    {
        return $this->call('/v1/catalogs/products/' . $id, 'GET');
    }

    public function createPlan(array $data): array
    {
        return $this->call('/v1/billing/plans', 'POST', $data);
    }

    public function createSubscription(array $data): array
    {
        return $this->call('/v1/billing/subscriptions', 'POST', $data);
    }

    public function cancelSubscription(string $subscriptionId, string $reason)
    {
        $this->call("/v1/billing/subscriptions/{$subscriptionId}/cancel", 'POST', ['reason' => $reason]);
    }

    public function getSubscriptionDetails(string $subscriptionId): array
    {
        return $this->call('/v1/billing/subscriptions/' . $subscriptionId, 'GET');
    }

    public function getPlanDetails(string $planId): array
    {
        return $this->call('/v1/billing/plans/' . $planId, 'GET');
    }

    public function verifyWebHook(array $data): array
    {
        return $this->call('/v1/notifications/verify-webhook-signature', 'POST', $data);
    }

    /**
     * @param string $url
     * @param array $eventTypes
     * @return string Webhook Id
     */
    public function createWebhook(string $url, array $eventTypes): string
    {
        $eventsConfig = [];
        foreach ($eventTypes as $type) {
            $eventsConfig [] = ['name' => $type];
        }
        $params = [
            'url' => $url,
            'event_types' => $eventsConfig
        ];

        $result = $this->call('/v1/notifications/webhooks', 'POST', $params);

        return $result['id'];
    }
}
