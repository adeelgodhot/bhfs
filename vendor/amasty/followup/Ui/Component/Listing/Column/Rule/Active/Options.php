<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Ui\Component\Listing\Column\Rule\Active;

use Magento\Framework\Data\OptionSourceInterface;
use Amasty\Followup\Model\Rule as Rule;
/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    public function toArray()
    {
        return [
            Rule::RULE_ACTIVE => __("Active"),
            Rule::RULE_INACTIVE => __("Inactive"),
        ];
    }

    public function toOptionArray()
    {
        return array(
            array(
                'value' => Rule::RULE_ACTIVE,
                'label' => __("Active")
            ),
            array(
                'value' => Rule::RULE_INACTIVE,
                'label' => __("Inactive")
            ),
        );
    }
}
