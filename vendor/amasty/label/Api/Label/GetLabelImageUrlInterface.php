<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Api\Label;

use Amasty\Label\Api\Data\LabelInterface;

/**
 * @api
 */
interface GetLabelImageUrlInterface
{
    /**
     * @param string|null $imageName
     * @return string|null
     */
    public function execute(?string $imageName): ?string;

    /**
     * @param LabelInterface $label
     * @return string|null
     */
    public function getByLabel(LabelInterface $label): ?string;
}
