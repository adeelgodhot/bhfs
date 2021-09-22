<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Text\Processors;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Exceptions\TextRenderException;
use Amasty\Label\Model\Label\Text\ProcessorInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class SpecialDatesProcessor implements ProcessorInterface
{
    const DAYS_LEFT_FOR_SPECIAL_PRICE = 'SPDL';
    const HOUR_LEFT_FOR_SPECIAL_PRICE = 'SPHL';
    const SECONDS_IN_DAY = 86400;
    const SECONDS_IN_HOUR = 3600;

    /**
     * @var DateTime
     */
    private $dateTime;

    public function __construct(
        DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
    }

    public function getAcceptableVariables(): array
    {
        return [
            self::DAYS_LEFT_FOR_SPECIAL_PRICE,
            self::HOUR_LEFT_FOR_SPECIAL_PRICE
        ];
    }

    public function getVariableValue(string $variable, LabelInterface $label, ProductInterface $product): string
    {
        $difference = $this->calculateTimeDifferenceBetweenNowAndSpecialTo($product);

        switch ($variable) {
            case self::DAYS_LEFT_FOR_SPECIAL_PRICE:
                $result = floor($difference / self::SECONDS_IN_DAY);
                break;
            case self::HOUR_LEFT_FOR_SPECIAL_PRICE:
                $result = floor($difference / self::SECONDS_IN_HOUR);
                break;
            default:
                throw new TextRenderException(__('The passed variable could not be processed'));
        }

        return (string) $result;
    }

    private function calculateTimeDifferenceBetweenNowAndSpecialTo(ProductInterface $product): int
    {
        $diff = 0;
        $toDate = $product->getSpecialToDate();

        if ($toDate) {
            $currentTime = $this->dateTime->date();
            $diff = strtotime($toDate) - strtotime($currentTime);
        }

        return (int) $diff;
    }
}
