<?php

namespace MGS\Blog\Model\System\Config;

use Magento\Framework\Option\ArrayInterface;

class Mode implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'grid',
                'label' => __('Grid')
            ],
            [
                'value' => 'list',
                'label' => __('List')
            ]
        ];
    }

}
