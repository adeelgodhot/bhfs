<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Data;

/**
 * Interface FeeInterface
 */
interface FeeInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const QUOTE_ID = 'quote_id';
    const AMOUNT = 'amount';
    const BASE_AMOUNT = 'base_amount';
    /**#@-*/

    /**
     * @return int|null
     */
    public function getEntityId();

    /**
     * @param int|null $entityId
     *
     * @return FeeInterface
     */
    public function setEntityId($entityId): FeeInterface;

    /**
     * @return int
     */
    public function getQuoteId(): int;

    /**
     * @param int $quoteId
     *
     * @return FeeInterface
     */
    public function setQuoteId(int $quoteId): FeeInterface;

    /**
     * @return float
     */
    public function getAmount(): float;

    /**
     * @param float $amount
     *
     * @return FeeInterface
     */
    public function setAmount(float $amount): FeeInterface;

    /**
     * @return float
     */
    public function getBaseAmount(): float;

    /**
     * @param float $baseAmount
     *
     * @return FeeInterface
     */
    public function setBaseAmount(float $baseAmount): FeeInterface;
}
