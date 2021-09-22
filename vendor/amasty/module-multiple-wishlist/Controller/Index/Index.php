<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Controller\Index;

use Amasty\MWishlist\Controller\AbstractIndexInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Wishlist\Controller\AbstractIndex as WishlistAbstractIndex;

class Index extends WishlistAbstractIndex implements AbstractIndexInterface
{
    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Page $page */
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->setPageTitle($page);

        return $page;
    }

    /**
     * @param Page $page
     */
    private function setPageTitle(Page $page)
    {
        $wishlistTitle = __('My Wish Lists');
        if (($titleBlock = $page->getLayout()->getBlock('page.main.title'))
            && method_exists($titleBlock, 'setPageTitle')
        ) {
            $titleBlock->setPageTitle($wishlistTitle);
        }
        $page->getConfig()->getTitle()->set($wishlistTitle);
    }
}
