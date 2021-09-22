<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Model\Source;

/**
 * Source for getting label for list of wishlists.
 *
 * Class ListType
 */
class ListType
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            Type::WISH => __('Wish Lists'),
            Type::REQUISITION => __('Requisition Lists')
        ];
    }
}
