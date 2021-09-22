<?php
namespace Magecomp\Qtydropdown\Model\Config;
class Qtytype implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Increment By Value')],
            ['value' => 1, 'label' => __('Custom Value')]
        ];
    }
}
