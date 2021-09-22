<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\Operation;

use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Repository\ScheduleRepository;
use Amasty\RecurringPayments\Model\Subscription\EmailNotifier;

class SaveCancelStatus
{

    /**
     * @var RepositoryInterface
     */
    private $subscriptionRepository;

    /**
     * @var ScheduleRepository
     */
    private $scheduleRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var EmailNotifier
     */
    private $emailNotifier;

    public function __construct(
        RepositoryInterface $subscriptionRepository,
        ScheduleRepository $scheduleRepository,
        Config $config,
        EmailNotifier $emailNotifier
    ) {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->scheduleRepository = $scheduleRepository;
        $this->config = $config;
        $this->emailNotifier = $emailNotifier;
    }

    /**
     * @param SubscriptionInterface $subscription
     */
    public function execute(SubscriptionInterface $subscription)
    {
        $subscription->setStatus(SubscriptionInterface::STATUS_CANCELED);
        $this->subscriptionRepository->save($subscription);

        $this->scheduleRepository->massDeletePendingJobsBySubscriptionId(
            $subscription->getSubscriptionId()
        );

        if ($this->config->isNotifySubscriptionCanceled((int)$subscription->getStoreId())) {
            $template = $this->config->getEmailTemplateSubscriptionCanceled((int)$subscription->getStoreId());
            $this->emailNotifier->sendEmail(
                $subscription,
                $template
            );
        }
    }
}
