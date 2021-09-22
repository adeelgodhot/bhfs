<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Ui\Component\Listing\Column\History\Status;

use Magento\Framework\Data\OptionSourceInterface;
use Amasty\Followup\Model\History as History;
/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    public function toArray()
    {
        return [
            History::STATUS_PROCESSING => __('Processing'),
            History::STATUS_SENT => __('Sent'),
            History::STATUS_CANCEL => __('Cancel'),
            History::STATUS_NO_PRODUCT => __('No Product'),
            History::STATUS_NO_CROSSEL_PRODUCT => __('No Crossel Products'),
        ];
    }

    public function toOptionArray()
    {
        return [
            [
                'value' => History::STATUS_PROCESSING,
                'label' => __("Processing")
            ],
            [
                'value' => History::STATUS_SENT,
                'label' => __("Sent")
            ],
            [
                'value' => History::STATUS_CANCEL,
                'label' => __("Cancel")
            ],
            [
                'value' => History::STATUS_NO_PRODUCT,
                'label' => __("No Product")
            ],
            [
                'value' => History::STATUS_NO_CROSSEL_PRODUCT,
                'label' => __("No Crossel Products")
            ],
        ];
    }
}
