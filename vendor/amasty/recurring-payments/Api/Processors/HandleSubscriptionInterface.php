<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Processors;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;

interface HandleSubscriptionInterface
{
    /**
     * @param SubscriptionInterface $subscription
     * @return void
     */
    public function process(SubscriptionInterface $subscription);
}
