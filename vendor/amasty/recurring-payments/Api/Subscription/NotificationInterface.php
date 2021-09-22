<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Subscription;

interface NotificationInterface
{
    const INTERVAL = 'interval';
    const INITIAL_FEE = 'initial_fee';
    const REGULAR_PAYMENT = 'regular_payment';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const PAYMENT_WITH_DISCOUNT = 'payment_with_discount';
    const DISCOUNT_CYCLE = 'discount_cycle';
    const TRIAL_STATUS = 'trial_status';
    const TRIAL_DAYS = 'trial_days';
    const START_DATE = 'start_date';
}
