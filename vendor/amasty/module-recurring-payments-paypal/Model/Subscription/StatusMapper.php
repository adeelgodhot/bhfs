<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\Subscription;

class StatusMapper
{
    const APPROVAL_PENDING = 'APPROVAL_PENDING';
    const ACTIVE = 'ACTIVE';
    const CANCELLED = 'CANCELLED';

    public function getStatus(string $status): string
    {
        $names = [
            self::APPROVAL_PENDING => __('Approval Pending'),
            self::ACTIVE           => __('Active'),
            self::CANCELLED        => __('Cancelled'),
        ];

        return (string)($names[$status] ?? $status);
    }
}
