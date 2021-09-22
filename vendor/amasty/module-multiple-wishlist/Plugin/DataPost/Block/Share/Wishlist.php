<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Plugin\DataPost\Block\Share;

use Amasty\MWishlist\Plugin\DataPost\Replacer;
use Magento\Wishlist\Block\Share\Wishlist as SharedBlock;

class Wishlist extends Replacer
{
    /**
     * @param SharedBlock $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(SharedBlock $subject, $result)
    {
        $this->dataPostReplace($result, static::WISHLIST_REGEX);

        return $result;
    }
}
