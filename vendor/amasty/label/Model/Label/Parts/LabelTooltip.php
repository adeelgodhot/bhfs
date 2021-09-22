<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Parts;

use Amasty\Label\Api\Data\LabelTooltipInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class LabelTooltip extends AbstractExtensibleModel implements LabelTooltipInterface
{
    public function getStatus(): int
    {
        return (int) $this->_getData(self::STATUS);
    }

    public function setStatus(int $status): void
    {
        $this->setData(self::STATUS, $status);
    }

    public function getColor(): ?string
    {
        return $this->_getData(self::COLOR);
    }

    public function setColor(string $color): void
    {
        $this->setData(self::COLOR, $color);
    }

    public function getTextColor(): ?string
    {
        return $this->_getData(self::TEXT_COLOR);
    }

    public function setTextColor(string $textColor): void
    {
        $this->setData(self::TEXT_COLOR, $textColor);
    }

    public function getText(): ?string
    {
        return $this->_getData(self::TEXT);
    }

    public function setText(string $text): void
    {
        $this->setData(self::TEXT, $text);
    }
}
