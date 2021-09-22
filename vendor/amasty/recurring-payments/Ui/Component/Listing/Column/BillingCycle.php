<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Ui\Component\Listing\Column;

use Amasty\RecurringPayments\Model\Subscription\Mapper\BillingFrequencyLabelMapper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class BillingCycle extends Column
{
    /**
     * @var BillingFrequencyLabelMapper
     */
    private $billingFrequencyLabelMapper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        BillingFrequencyLabelMapper $billingFrequencyLabelMapper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->billingFrequencyLabelMapper = $billingFrequencyLabelMapper;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $frequency = isset($item['frequency']) ? (int)$item['frequency'] : 0;
                $frequencyUnit = isset($item['frequency_unit']) ? (string)$item['frequency_unit'] : '';

                $item[$this->getData('name')] = $this->billingFrequencyLabelMapper->getLabel(
                    $frequency,
                    $frequencyUnit
                );
            }
        }

        return $dataSource;
    }
}
