<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Api\Data;

/**
 * @api
 */
interface LabelFrontendSettingsInterface
{
    const PART_CODE = 'frontend_settings';

    const TYPE = 'type';
    const LABEL_TEXT = 'label_text';
    const IMAGE = 'image';
    const IMAGE_SIZE = 'image_size';
    const POSITION = 'position';
    const STYLE = 'style';

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @param int $type
     * @return void
     */
    public function setType(int $type): void;

    /**
     * @return string
     */
    public function getLabelText(): string;

    /**
     * @param string $labelText
     * @return void
     */
    public function setLabelText(string $labelText): void;

    /**
     * @return string|null
     */
    public function getImage(): ?string;

    /**
     * @param string|null $image
     * @return void
     */
    public function setImage(?string $image): void;

    /**
     * @return string|null
     */
    public function getImageSize(): ?string;

    /**
     * @param string|null $imageSize
     * @return void
     */
    public function setImageSize(?string $imageSize): void;

    /**
     * @return int
     */
    public function getPosition(): int;

    /**
     * @param int $imagePosition
     * @return void
     */
    public function setPosition(int $imagePosition): void;

    /**
     * @return string|null
     */
    public function getStyle(): ?string;

    /**
     * @param string|null $style
     * @return void
     */
    public function setStyle(?string $style): void;
}
