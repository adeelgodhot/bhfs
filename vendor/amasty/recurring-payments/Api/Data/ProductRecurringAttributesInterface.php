<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Data;

interface ProductRecurringAttributesInterface
{
    const RECURRING_ENABLE = 'am_recurring_enable';
    const SUBSCRIPTION_ONLY = 'am_subscription_only';
    const BILLING_CYCLE = 'am_billing_cycle';
    const BILLING_FREQUENCY = 'am_billing_frequency';
    const BILLING_FREQUENCY_UNIT = 'am_billing_frequency_unit';
    const ENABLE_FREE_TRIALS = 'am_enable_free_trials';
    const NUMBER_TRIAL_DAYS = 'am_number_trial_days';
    const ENABLE_CHARGE_FEE = 'am_enable_charge_fee';
    const INITIAL_FEE_TYPE = 'am_initial_fee_type';
    const INITIAL_FEE_AMOUNT = 'am_initial_fee_amount';
    const ENABLE_DISCOUNTED_PRICES = 'am_enable_discounted_prices';
    const DISCOUNT_TYPE = 'am_discount_type';
    const DISCOUNT_AMOUNT = 'am_discount_amount';
    const DISCOUNT_PERCENT = 'am_discount_percent';
    const ENABLE_LIMIT_DISCOUNT_CYCLE = 'am_enable_limit_discount_cycle';
    const LIMIT_DISCOUNT_CYCLE = 'am_limit_discount_cycle';
    const START_DATE = 'am_rec_start_date';
    const END_DATE = 'am_rec_end_date';
    const COUNT_CYCLES = 'am_rec_count_cycles';
    const TIMEZONE = 'am_rec_timezone';
    const PLANS = 'am_rec_plans';
    const SUBSCRIPTION_PLAN_ID = 'am_rec_subscription_plan_id';
}
