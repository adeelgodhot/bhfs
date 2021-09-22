<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;

class Popular extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_MWishlist::wishlist_popular_items';

    /**
     * @return void
     */
    public function execute()
    {
        $this->initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Most Wanted Items in Lists'));
        $this->_view->renderLayout();
    }

    /**
     * Initiate action
     *
     * @return $this
     */
    private function initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(self::ADMIN_RESOURCE)
            ->_addBreadcrumb(__('Most Wanted Items in Lists'), __('Most Wanted Items in Lists'));

        return $this;
    }
}
