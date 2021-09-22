<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\WebHook;

use Amasty\RecurringPaypal\Api\WebHook\HandlerInterface;
use Amasty\RecurringPaypal\Api\WebHook\ProcessorInterface;
use Amasty\RecurringPaypal\Model\Api\Adapter;
use Amasty\RecurringPaypal\Model\ConfigProvider;
use Amasty\RecurringPaypal\Model\Subscription\Cache as SubscriptionCache;
use Magento\Framework\App\RequestInterface;

class Processor implements ProcessorInterface
{
    const VERIFICATION_STATUS_SUCCESS = 'SUCCESS';
    const VERIFICATION_STATUS_FAILURE = 'FAILURE';
    const SUBSCRIPTION_EVENT_PREFIX = 'BILLING.SUBSCRIPTION.';

    const VERIFICATION_HEADERS = [
        'auth_algo',
        'cert_url',
        'transmission_id',
        'transmission_sig',
        'transmission_time',
    ];
    const PAYPAL_HEADER_PREFIX = 'paypal_';

    /**
     * @var HandlerInterface[]
     */
    private $handlers;

    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var ConfigProvider
     */
    private $config;

    /**
     * @var SubscriptionCache
     */
    private $subscriptionCache;

    public function __construct(
        Adapter $adapter,
        ConfigProvider $config,
        SubscriptionCache $subscriptionCache,
        array $handlers
    ) {
        $this->handlers = $handlers;
        $this->adapter = $adapter;
        $this->config = $config;
        $this->subscriptionCache = $subscriptionCache;
    }

    public function processRequest(RequestInterface $request)
    {
        if (!$this->config->getPaypalWebhookId()) {
            throw new \RuntimeException('Failed to process Paypal webhook: Webhook id is not specified');
        }

        $payload = $request->getContent();
        if (!$payload
            || !($payload = json_decode($payload, true))
            || !$this->isRequestValid($request, $payload)
        ) {
            throw new \RuntimeException('Bad request');
        }

        $eventType = $payload['event_type'];

        if (0 === strpos(self::SUBSCRIPTION_EVENT_PREFIX, $eventType)) {
            $this->subscriptionCache->clearSubscriptionData($payload['resource']['id']);
        }

        if (!isset($this->handlers[$eventType])) {
            return;
        }

        $this->handlers[$eventType]->process($payload);
    }

    private function isRequestValid(RequestInterface $request, $payload): bool
    {
        $params = [];
        foreach (self::VERIFICATION_HEADERS as $header) {
            $params[$header] = $request->getHeader(self::PAYPAL_HEADER_PREFIX . $header);
        }
        $params['webhook_id'] = $this->config->getPaypalWebhookId();
        $params['webhook_event'] = $payload;
        $result = $this->adapter->verifyWebHook($params);

        return $result['verification_status'] == self::VERIFICATION_STATUS_SUCCESS;
    }
}
