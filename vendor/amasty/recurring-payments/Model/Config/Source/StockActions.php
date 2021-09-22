<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Config\Source;

use Amasty\RecurringPayments\Model\AbstractArray;

/**
 * Class StockActions
 */
class StockActions extends AbstractArray
{
    const NOTHING = 'nothing';
    const PAUSE = 'pause';
    const CANCEL = 'cancel';

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $options = [
            self::NOTHING => __('Do Nothing'),
            self::PAUSE => __('Pause Subscription'),
            self::CANCEL => __('Cancel Subscription'),
        ];

        return $options;
    }
}
