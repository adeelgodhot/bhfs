<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Parts;

use Amasty\Label\Api\Data\LabelFrontendSettingsInterface;
use Amasty\Label\Model\ResourceModel\Label\Collection;
use Magento\Framework\Model\AbstractExtensibleModel;

class FrontendSettings extends AbstractExtensibleModel implements LabelFrontendSettingsInterface
{
    public function getType(): int
    {
        $type = (int) $this->getData(self::TYPE);

        return $type === Collection::MODE_LIST ? $type : Collection::MODE_PDP;
    }

    public function setType(int $type): void
    {
        $this->setData(self::TYPE, $type);
    }

    public function getLabelText(): string
    {
        return (string) $this->_getData(self::LABEL_TEXT);
    }

    public function setLabelText(string $labelText): void
    {
        $this->setData(self::LABEL_TEXT, $labelText);
    }

    public function getImage(): ?string
    {
        $image = $this->_getData(self::IMAGE);

        return is_string($image) ? $image : null;
    }

    public function setImage(?string $image): void
    {
        $this->setData(self::IMAGE, $image);
    }

    public function getImageSize(): ?string
    {
        $imageSize = $this->_getData(self::IMAGE_SIZE);

        return is_string($imageSize) ? $imageSize : null;
    }

    public function setImageSize(?string $imageSize): void
    {
        $this->setData(self::IMAGE_SIZE, $imageSize);
    }

    public function getPosition(): int
    {
        return (int) $this->_getData(self::POSITION);
    }

    public function setPosition(int $imagePosition): void
    {
        $this->setData(self::POSITION, $imagePosition);
    }

    public function getStyle(): ?string
    {
        $style = $this->_getData(self::STYLE);

        return is_string($style) ? $style : null;
    }

    public function setStyle(?string $style): void
    {
        $this->setData(self::STYLE, $style);
    }
}
