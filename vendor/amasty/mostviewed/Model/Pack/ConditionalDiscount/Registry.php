<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Model\Pack\ConditionalDiscount;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;

class Registry
{
    /**
     * @var array
     */
    private $cache = [];

    public function save(ConditionalDiscountInterface $conditionalDiscount): void
    {
        if (!isset($this->cache[$conditionalDiscount->getId()])) {
            $this->cache[$conditionalDiscount->getId()] = $conditionalDiscount;
        }
    }

    public function get(int $id): ?ConditionalDiscountInterface
    {
        return $this->cache[$id] ?? null;
    }
}
