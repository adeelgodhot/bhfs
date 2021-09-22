<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\ViewModel;

use Magento\Catalog\Model\Product;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Wishlist\Model\Item;

class PostHelper implements ArgumentInterface
{
    const LIST_WISHLIST_ROUTE = 'mwishlist/index/index';
    const DELETE_WISHLIST_ROUTE = 'mwishlist/wishlist/delete';
    const CREATE_WISHLIST_ROUTE = 'mwishlist/wishlist/create';
    const VIEW_WISHLIST_ROUTE = 'mwishlist/wishlist/index';
    const VALIDATE_WISHLIST_NAME_ROUTE = 'mwishlist/wishlist/validateWishlistName';
    const SEND_WISHLIST = 'wishlist/index/send';
    const UPDATE_WISHLIST_ROUTE = 'mwishlist/wishlist/update';

    const CONFIGURE_ITEM_ROUTE = 'wishlist/index/configure';
    const ADD_ITEM_ROUTE = 'mwishlist/item/add';
    const IN_CART_ITEMS_ROUTE = 'wishlist/index/allcart';
    const IN_CART_ITEM_ROUTE = 'mwishlist/item/toCart';
    const MOVE_ITEMS_ROUTE = 'mwishlist/item/move';
    const COPY_ITEMS_ROUTE = 'mwishlist/item/copy';
    const REMOVE_ITEMS_ROUTE = 'mwishlist/item/remove';
    const FROM_CART_ITEM_ROUTE = 'mwishlist/item/fromCart';

    const PRODUCT_SEARCH_ROUTE = 'mwishlist/product/search';

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        ModuleManager $moduleManager,
        JsonSerializer $jsonSerializer
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param string $action
     * @param array $data
     * @param string|null $redirect
     * @return string
     */
    public function getPostData(string $action, array $data, ?string $redirect = null): string
    {
        $data = ['action' => $action, 'data' => $data];
        if ($redirect !== null) {
            $data['redirect'] = $redirect;
        }

        return $this->jsonSerializer->serialize($data);
    }

    /**
     * Get cart URL parameters
     *
     * @param string|Product|Item $item
     * @return array
     */
    public function getCartItemParams($item): array
    {
        $params = [
            'item' => is_string($item) ? $item : $item->getWishlistItemId()
        ];
        if ($item instanceof Item) {
            $params['qty'] = $item->getQty();
        }

        return $params;
    }

    /**
     * @return ModuleManager
     */
    public function getModuleManager(): ModuleManager
    {
        return $this->moduleManager;
    }
}
