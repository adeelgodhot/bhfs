<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Save\Preprocessors;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\Label\Save\DataPreprocessorInterface;
use DateTimeImmutable as DateTimeImmutable;
use Magento\Framework\Stdlib\DateTime\Filter\DateTime;

class FilterActiveFromToDates implements DataPreprocessorInterface
{
    /**
     * @var DateTime
     */
    private $dateFilter;

    public function __construct(
        DateTime $dateFilter
    ) {
        $this->dateFilter = $dateFilter;
    }

    public function process(array $data): array
    {
        foreach ([LabelInterface::ACTIVE_FROM, LabelInterface::ACTIVE_TO] as $dateKey) {
            if (!empty($data[$dateKey])) {
                $inputFilter = new \Zend_Filter_Input([$dateKey => $this->dateFilter], [], $data);
                $data = $inputFilter->getUnescaped();
            }
        }

        if (!empty($data[LabelInterface::ACTIVE_FROM])) {
            $isActive = $data[LabelInterface::STATUS] ?? false;
            $dateTo = empty($data[LabelInterface::ACTIVE_TO])
                ? new DateTimeImmutable()
                : new DateTimeImmutable($data[LabelInterface::ACTIVE_TO]);
            $now = new DateTimeImmutable();
            $data[LabelInterface::STATUS] = $isActive && ($dateTo->getTimestamp() > $now->getTimestamp());
        }

        return $data;
    }
}
