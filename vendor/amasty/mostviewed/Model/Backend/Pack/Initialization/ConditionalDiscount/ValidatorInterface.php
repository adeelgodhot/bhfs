<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Backend\Pack\Initialization\ConditionalDiscount;

use Magento\Framework\Exception\LocalizedException;

interface ValidatorInterface
{
    /**
     * Validate all conditional discounts data.
     *
     * @param array $discountsData
     * @return void
     * @throws LocalizedException
     */
    public function validate(array $discountsData): void;
}
