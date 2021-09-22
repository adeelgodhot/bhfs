<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Api\Data;

interface LabelTooltipInterface
{
    const PART_CODE = 'label_tooltip';

    const STATUS = 'status';
    const COLOR = 'color';
    const TEXT_COLOR = 'text_color';
    const TEXT = 'text';

    /**
     * @return int
     */
    public function getStatus(): int;

    /**
     * @param int $status
     * @return void
     */
    public function setStatus(int $status): void;

    /**
     * @return string|null
     */
    public function getColor(): ?string;

    /**
     * @param string $color
     * @return void
     */
    public function setColor(string $color): void;

    /**
     * @return string|null
     */
    public function getTextColor(): ?string;

    /**
     * @param string $textColor
     * @return void
     */
    public function setTextColor(string $textColor): void;

    /**
     * @return string|null
     */
    public function getText(): ?string;

    /**
     * @param string $text
     * @return void
     */
    public function setText(string $text): void;
}
