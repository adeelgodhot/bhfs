<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Block\Account\Wishlist\Item\Column;

use Amasty\MWishlist\Block\AbstractPostBlock;
use Amasty\MWishlist\Controller\UpdateAction;
use Amasty\MWishlist\ViewModel\PostHelper;
use Magento\Wishlist\Block\AbstractBlock;

class Remove extends AbstractBlock
{
    /**
     * @return string
     */
    public function getPostData(): string
    {
        return $this->getPostHelper()->getPostData($this->getUrl(PostHelper::REMOVE_ITEMS_ROUTE), [
            'item' => $this->getItem()->getId(),
            'wishlist_id' => $this->getItem()->getWishlistId(),
            UpdateAction::COMPONENT_PARAM => 'itemsQty',
            UpdateAction::BLOCK_PARAM => 'customer.wishlist'
        ]);
    }

    /**
     * @return PostHelper
     */
    public function getPostHelper(): PostHelper
    {
        return $this->_data[AbstractPostBlock::POST_HELPER_KEY];
    }
}
