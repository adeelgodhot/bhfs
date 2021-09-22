<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Birth
 */
namespace Amasty\Birth\Model\Source;

class Group extends \Magento\Customer\Model\Customer\Attribute\Source\Group
    implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift(
            $options,
            [
                'value' => 0,
                'label' => __('NOT LOGEED IN')
            ]
        );

        return $options;
    }
}
