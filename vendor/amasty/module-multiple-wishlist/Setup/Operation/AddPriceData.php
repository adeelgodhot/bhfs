<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Setup\Operation;

use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Wishlist\Model\ResourceModel\Item\Collection as WishlistItemCollection;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory as WishlistItemCollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist\Collection as WishlistCollection;
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory as WishlistCollectionFactory;

class AddPriceData
{
    /**
     * @var WishlistItemCollection
     */
    private $wishlistItemCollection;
    /**
     * @var WishlistCollection
     */
    private $wishlistCollection;

    /**
     * @var CustomerCollection
     */
    private $customerCollection;

    public function __construct(
        WishlistItemCollectionFactory $wishlistItemCollectionFactory,
        WishlistCollectionFactory $wishlistCollectionFactory,
        CustomerCollectionFactory $customerCollectionFactory
    ) {
        $this->wishlistItemCollection = $wishlistItemCollectionFactory->create();
        $this->wishlistCollection = $wishlistCollectionFactory->create();
        $this->customerCollection = $customerCollectionFactory->create();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    public function execute(ModuleDataSetupInterface $setup): void
    {
        $customerIds = array_unique($this->wishlistCollection->getColumnValues('customer_id'));
        $this->customerCollection->addAttributeToFilter('entity_id', $customerIds);
        foreach ($this->wishlistCollection as $wishlist) {
            $customerGroupId = $this->customerCollection->getItemById($wishlist->getCustomerId())->getGroupId();
            foreach ($wishlist->getItemCollection() as $item) {
                $price = $item->getProduct()->setCustomerGroupId($customerGroupId)->getFinalPrice($item->getQty());
                $item->setProductPrice($price);
            }
            $this->wishlistCollection->save();
        }
    }
}
