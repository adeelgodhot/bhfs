<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;

class HelperContext implements ArgumentInterface
{
    /**
     * @var WishlistHelper
     */
    private $wishlistHelper;

    public function __construct(WishlistHelper $wishlistHelper)
    {
        $this->wishlistHelper = $wishlistHelper;
    }

    /**
     * @return WishlistHelper
     */
    public function getWishlistHelper(): WishlistHelper
    {
        return $this->wishlistHelper;
    }
}
