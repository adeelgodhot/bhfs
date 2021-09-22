<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


namespace Amasty\MWishlist\Plugin\DataPost\Block\Widget;

use Amasty\MWishlist\Plugin\DataPost\Replacer;
use Magento\CatalogWidget\Block\Product\ProductsList as WidgetList;

class ProductsList extends Replacer
{
    /**
     * @param WidgetList $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(WidgetList $subject, $result)
    {
        $this->dataPostReplace($result, static::WISHLIST_REGEX);

        return $result;
    }
}
