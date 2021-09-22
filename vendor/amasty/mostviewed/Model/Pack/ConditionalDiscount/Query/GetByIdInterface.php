<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Query;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface GetByIdInterface
{
    /**
     * @param int $id
     * @return ConditionalDiscountInterface
     * @throws NoSuchEntityException
     */
    public function execute(int $id): ConditionalDiscountInterface;
}
