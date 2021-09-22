<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\IpnHandlers;

use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\Subscription\EmailNotifier;
use Amasty\RecurringStripe\Api\IpnHandlerInterface;
use Amasty\RecurringPayments\Model\Config;
use Magento\Framework\Exception\NoSuchEntityException;

abstract class AbstractIpnHandler implements IpnHandlerInterface
{
    const CENTS = 100;

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
    private $subscriptionRepository;

    public function __construct(
        Config $config,
        EmailNotifier $emailNotifier,
        RepositoryInterface $subscriptionRepository
    ) {
        $this->config = $config;
        $this->emailNotifier = $emailNotifier;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * @param \Stripe\Event $event
     * @return \Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface|null
     */
    protected function getSubscription(\Stripe\Event $event)
    {
        $object = $event->data->object;
        if ($object instanceof \Stripe\Invoice) {
            $subscriptionId = $object->subscription;
        } else {
            $subscriptionId = $object->id;
        }

        if (!$subscriptionId) {
            return null;
        }
        try {
            return $this->subscriptionRepository->getBySubscriptionId($subscriptionId);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
