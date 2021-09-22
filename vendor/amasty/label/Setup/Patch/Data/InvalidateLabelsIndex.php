<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Setup\Patch\Data;

class InvalidateLabelsIndex extends InvalidateLabelIndex
{
    public static function getDependencies(): array
    {
        return [
            \Amasty\Label\Setup\Patch\Data\InvalidateLabelIndex::class
        ];
    }
}
