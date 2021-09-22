<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Subscription;

/**
 * Interface ProcessorInterface
 */
interface GridInterface
{
    /**
     * You must return array of subscriptions
     * array of object \Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface
     *
     * @param int $customerId
     *
     * @return \Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface[]
     */
    public function process(int $customerId);
}
