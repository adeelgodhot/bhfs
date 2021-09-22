<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


namespace Amasty\MWishlist\Plugin\DataPost\Block\Product;

use Amasty\MWishlist\Plugin\DataPost\Replacer;
use Magento\Catalog\Block\Product\Compare\ListCompare as ProductListCompare;

class ListCompare extends Replacer
{
    /**
     * @param ProductListCompare $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(ProductListCompare $subject, $result)
    {
        $this->dataPostReplace($result);

        return $result;
    }
}
