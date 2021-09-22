<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Block;

use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Framework\View\Element\Template;

abstract class AbstractPostBlock extends Template
{
    const POST_HELPER_KEY = 'post_helper';

    /**
     * @return PostHelper
     */
    public function getPostHelper(): PostHelper
    {
        return $this->_data[self::POST_HELPER_KEY];
    }
}
