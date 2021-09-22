<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Block\Account\Wishlist;

use Amasty\MWishlist\ViewModel\Tabs as TabsHelper;
use Magento\Framework\View\Element\Template;

class WishlistList extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_MWishlist::wishlist/list/wishlist.phtml';

    /**
     * @var int|null
     */
    private $activeTabId;

    public function getTabs(): array
    {
        return $this->getTabsHelper()->getTabs();
    }

    public function isActiveTab(int $tabId): bool
    {
        if ($this->activeTabId === null) {
            $this->activeTabId = $this->getTabsHelper()->resolveActiveTabId();
        }

        return $tabId === $this->activeTabId;
    }

    public function getTabsHelper(): TabsHelper
    {
        return $this->_data['tabs_helper'];
    }
}
