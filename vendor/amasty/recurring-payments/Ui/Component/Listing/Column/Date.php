<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Ui\Component\Listing\Column;

use Amasty\RecurringPayments\Model\Date as DateModel;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Date extends \Magento\Ui\Component\Listing\Columns\Date
{
    /**
     * @var DateModel
     */
    private $date;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $timezone,
        BooleanUtils $booleanUtils,
        DateModel $date,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $timezone, $booleanUtils, $components, $data);
        $this->date = $date;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])
                    && $item[$this->getData('name')] !== "0000-00-00 00:00:00"
                ) {
                    $item[$this->getData('name')] = $this->date->convertDate(
                        $item[$this->getData('name')],
                        null,
                        \IntlDateFormatter::MEDIUM,
                        \IntlDateFormatter::MEDIUM
                    );
                }
            }
        }

        return $dataSource;
    }
}
