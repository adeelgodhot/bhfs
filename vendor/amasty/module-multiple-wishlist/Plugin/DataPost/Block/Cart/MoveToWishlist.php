<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Plugin\DataPost\Block\Cart;

use Amasty\MWishlist\Plugin\DataPost\Replacer;
use Magento\Wishlist\Block\Cart\Item\Renderer\Actions\MoveToWishlist as NativeMoveWishlist;

class MoveToWishlist extends Replacer
{
    /**
     * @param NativeMoveWishlist $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(NativeMoveWishlist $subject, $result)
    {
        $this->dataPostReplace($result);

        return $result;
    }
}
