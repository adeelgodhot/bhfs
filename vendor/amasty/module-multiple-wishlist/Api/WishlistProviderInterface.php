<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


namespace Amasty\MWishlist\Api;

use Amasty\MWishlist\Api\Data\WishlistInterface;

interface WishlistProviderInterface
{
    /**
     * Retrieve current wishlist
     *
     * @param int $wishlistId
     * @return WishlistInterface
     */
    public function getWishlist(?int $wishlistId = null);
}
