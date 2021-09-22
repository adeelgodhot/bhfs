<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Text;

use Amasty\Label\Api\Data\LabelInterface;

interface ZeroValueCheckerInterface
{
    public function isZeroValue(string $variableValue, LabelInterface $label): bool;
}
