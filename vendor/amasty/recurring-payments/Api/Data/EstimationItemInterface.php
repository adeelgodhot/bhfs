<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Data;

interface EstimationItemInterface
{
    const VALUE = 'value';
    const ITEM_ID = 'item_id';

    /**
     * @return float
     */
    public function getValue(): float;

    /**
     * @param float $value
     * @return void
     */
    public function setValue(float $value): void;

    /**
     * @return int
     */
    public function getItemId(): int;

    /**
     * @param int $itemId
     * @return void
     */
    public function setItemId(int $itemId): void;
}
