<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Backend\Pack\Initialization\ConditionalDiscount;

use Magento\Framework\Exception\LocalizedException;

interface ColumnValidatorInterface
{
    /**
     * @param string $columnName
     * @param string|null $value
     * @return void
     * @throws LocalizedException
     */
    public function validate(string $columnName, ?string $value): void;
}
