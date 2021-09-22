<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */


declare(strict_types=1);

namespace Amasty\Mostviewed\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ConditionalDiscountInterface extends ExtensibleDataInterface
{
    const MAIN_TABLE = 'amasty_mostviewed_pack_conditional_discounts';

    const ID = 'id';
    const PACK_ID = 'pack_id';
    const NUMBER_ITEMS = 'number_items';
    const DISCOUNT_AMOUNT = 'discount_amount';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return \Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getPackId(): int;

    /**
     * @param int $packId
     */
    public function setPackId(int $packId): void;

    /**
     * @return int
     */
    public function getNumberItems(): int;

    /**
     * @param int $numberItems
     */
    public function setNumberItems(int $numberItems): void;

    /**
     * @return float
     */
    public function getDiscountAmount(): float;

    /**
     * @param float $discountAmount
     */
    public function setDiscountAmount(float $discountAmount): void;
}
