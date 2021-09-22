<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Ui\Component\Listing\Column\History\Reason;

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
            History::REASON_BLACKLIST => __('Black List'),
            History::REASON_EVENT => __('Stop Event'),
            History::REASON_ADMIN => __('Removed by Admin'),
            History::REASON_NOT_SUBSCRIBED => __('Customer not subscribed'),
        ];
    }

    public function toOptionArray()
    {
        return array(
            array(
                'value' => History::REASON_BLACKLIST,
                'label' => __("Black List")
            ),
            array(
                'value' => History::REASON_EVENT,
                'label' => __("Stop Event")
            ),
            array(
                'value' => History::REASON_ADMIN,
                'label' => __("Removed by Admin")
            ),
            array(
                'value' => History::REASON_NOT_SUBSCRIBED,
                'label' => __("Customer not subscribed")
            ),
        );
    }
}
