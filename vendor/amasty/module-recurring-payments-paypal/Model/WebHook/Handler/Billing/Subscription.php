<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\WebHook\Handler\Billing;

use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Subscription\EmailNotifier;
use Amasty\RecurringPaypal\Model\Subscription\Cache as SubscriptionCache;
use Magento\Framework\Exception\NoSuchEntityException;

abstract class Subscription
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var EmailNotifier
     */
    protected $emailNotifier;

    /**
     * @var RepositoryInterface
     */
    protected $subscriptionRepository;

    /**
     * @var SubscriptionCache
     */
    private $subscriptionCache;

    public function __construct(
        Config $config,
        RepositoryInterface $subscriptionRepository,
        EmailNotifier $emailNotifier,
        SubscriptionCache $subscriptionCache
    ) {
        $this->config = $config;
        $this->emailNotifier = $emailNotifier;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->subscriptionCache = $subscriptionCache;
    }

    /**
     * @param array $payload
     * @return SubscriptionInterface|null
     */
    protected function getSubscription(array $payload)
    {
        $subscriptionId = $payload['resource']['id'];
        try {
            return $this->subscriptionRepository->getBySubscriptionId($subscriptionId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param SubscriptionInterface $subscription
     * @param string $template
     */
    protected function sendNotification(SubscriptionInterface $subscription, string $template)
    {
        $this->emailNotifier->sendEmail(
            $subscription,
            $template
        );
    }

    /**
     * @param string $subscriptionId
     */
    protected function clearCache(string $subscriptionId)
    {
        $this->subscriptionCache->clearSubscriptionData($subscriptionId);
    }
}
