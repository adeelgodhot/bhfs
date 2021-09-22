<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\WebHook\Handler\Billing\Subscription;

use Amasty\RecurringPaypal\Api\WebHook\HandlerInterface;
use Amasty\RecurringPaypal\Model\WebHook\Handler\Billing\Subscription;

class Cancelled extends Subscription implements HandlerInterface
{
    /**
     * @param array $payload
     */
    public function process(array $payload)
    {
        if (!($subscription = $this->getSubscription($payload))) {
            return;
        }

        $this->clearCache((string)$subscription->getSubscriptionId());

        if ($this->config->isNotifySubscriptionCanceled((int)$subscription->getStoreId())) {
            $template = $this->config->getEmailTemplateSubscriptionCanceled((int)$subscription->getStoreId());
            $this->sendNotification($subscription, $template);
        }
    }
}
