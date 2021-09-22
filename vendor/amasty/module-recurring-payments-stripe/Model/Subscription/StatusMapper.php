<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\Subscription;

class StatusMapper
{
    const TRIAL = 'trialing';
    const ACTIVE = 'active';
    const CANCELED = 'canceled';
    const INCOMPLETE_EXPIRED = 'incomplete_expired';
    const PAST_DUE = 'past_due';

    public function getStatus(string $status): string
    {
        $names = [
            self::TRIAL              => __('Trial'),
            self::ACTIVE             => __('Active'),
            self::CANCELED           => __('Canceled'),
            self::INCOMPLETE_EXPIRED => __('Incomplete And Expired'),
            self::PAST_DUE           => __('Past Due')
        ];

        return (string)($names[$status] ?? $status);
    }
}
