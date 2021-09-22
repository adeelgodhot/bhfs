<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Product\Source;

/**
 * Class AvailableSubscription
 */
class AvailableSubscription extends \Amasty\RecurringPayments\Model\AbstractArray
{
    const NO = 'no';
    const GLOBAL_SETTING = 'global_setting';
    const CUSTOM_SETTING = 'custom_setting';

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $options = [
            self::NO => __('No'),
            self::GLOBAL_SETTING => __('Yes, use global subscription settings'),
            self::CUSTOM_SETTING => __('Yes, use custom subscription settings'),
        ];

        return $options;
    }
}
