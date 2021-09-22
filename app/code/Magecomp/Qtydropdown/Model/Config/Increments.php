<?php
namespace Magecomp\Qtydropdown\Model\Config;
class Increments implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('1')],
            ['value' => 2, 'label' => __('2')],
            ['value' => 3, 'label' => __('3')],
            ['value' => 4, 'label' => __('4')],
			['value' => 5, 'label' => __('5')]
        ];
    }
}
