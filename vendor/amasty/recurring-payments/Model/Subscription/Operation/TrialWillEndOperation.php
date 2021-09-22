<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Subscription\Operation;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\Config;
use Amasty\RecurringPayments\Model\Subscription\EmailNotifier;

/**
 * Fired when trial will end soon
 */
class TrialWillEndOperation
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var EmailNotifier
     */
    private $emailNotifier;

    public function __construct(Config $config, EmailNotifier $emailNotifier)
    {
        $this->config = $config;
        $this->emailNotifier = $emailNotifier;
    }

    /**
     * @param SubscriptionInterface $subscription
     * @return bool
     */
    public function execute(SubscriptionInterface $subscription): bool
    {
        $storeId = (int)$subscription->getStoreId();
        if ($this->config->isNotifyTrialEnd($storeId)) {
            $template = $this->config->getEmailTemplateTrialEnd($storeId);
            $this->emailNotifier->sendEmail(
                $subscription,
                $template
            );
        }

        return true;
    }
}
