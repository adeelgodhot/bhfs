<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Component\Adminhtml\Queue\Column\Renderer;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Delay extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var \Amasty\Followup\Helper\Data
     */
    protected $amhelper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Amasty\Followup\Helper\Data $amhelper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->amprhelper = $amhelper;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])) {
                    $delayedStartValue = $item[$this->getData('name')];
                    $days = $this->amprhelper->getDays($delayedStartValue) ? $this->amprhelper->getDays($delayedStartValue) : 0 ;
                    $hours = $this->amprhelper->getHours($delayedStartValue) ? $this->amprhelper->getHours($delayedStartValue) : 0;
                    $minutes = $this->amprhelper->getMinutes($delayedStartValue) ? $this->amprhelper->getMinutes($delayedStartValue) : 0;

                    $result = '';
                    if ($days) {
                        $result = "$days days $hours hours $minutes minutes";
                    } elseif (!$days && $hours) {
                        $result = "$hours hours $minutes minutes";
                    } elseif (!$days && !$hours && $minutes) {
                        $result = "$minutes minutes";
                    }
                    $item[$this->getData('name')] = $result;
                }
            }
        }

        return $dataSource;
    }

}
