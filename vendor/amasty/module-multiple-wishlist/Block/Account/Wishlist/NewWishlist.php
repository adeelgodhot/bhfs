<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Block\Account\Wishlist;

use Amasty\MWishlist\Block\AbstractPostBlock;
use Amasty\MWishlist\Controller\UpdateAction;
use Amasty\MWishlist\ViewModel\PostHelper;

class NewWishlist extends AbstractPostBlock
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_MWishlist::wishlist/new.phtml';

    /**
     * @return string
     */
    public function getValidateWishlistAction(): string
    {
        return $this->getUrl(PostHelper::VALIDATE_WISHLIST_NAME_ROUTE);
    }

    /**
     * @return string
     */
    public function getPostData(): string
    {
        return $this->getPostHelper()->getPostData($this->getUrl(PostHelper::CREATE_WISHLIST_ROUTE), [
            UpdateAction::BLOCK_PARAM => 'mwishlist.list.contrainer'
        ]);
    }
}
