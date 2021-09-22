<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Plugin\Xsearch\Block\Search;

use Amasty\MWishlist\Plugin\DataPost\Replacer;

class Product extends Replacer
{
    public function afterToHtml($subject, string $result) : string
    {
        $this->dataPostReplace($result);

        return $result;
    }
}
