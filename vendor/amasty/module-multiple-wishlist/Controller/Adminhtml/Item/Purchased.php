<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;

class Purchased extends Action
{
    const ADMIN_RESOURCE = 'Amasty_MWishlist::wishlist_most_purchased_items';

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Most Purchased Items from Lists'));
        $this->_view->renderLayout();
    }

    /**
     * Initiate action
     *
     * @return $this
     */
    private function initAction(): void
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(self::ADMIN_RESOURCE)
            ->_addBreadcrumb(__('Most Purchased Items from Lists'), __('Most Purchased Items from Lists'));
    }
}
