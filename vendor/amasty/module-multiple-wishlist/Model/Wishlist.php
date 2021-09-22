<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Model;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Magento\Wishlist\Model\Wishlist as NativeWishlist;

class Wishlist extends NativeWishlist implements WishlistInterface
{
    /**
     * @inheritdoc
     */
    public function getWishlistId()
    {
        return (int) $this->_getData(WishlistInterface::WISHLIST_ID);
    }

    /**
     * @inheritdoc
     */
    public function setWishlistId($wishlistId)
    {
        $this->setData(WishlistInterface::WISHLIST_ID, $wishlistId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getShared()
    {
        return $this->_getData(WishlistInterface::SHARED);
    }

    /**
     * @inheritdoc
     */
    public function setShared($shared)
    {
        $this->setData(WishlistInterface::SHARED, $shared);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSharingCode()
    {
        return $this->_getData(WishlistInterface::SHARING_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setSharingCode(string $sharingCode)
    {
        $this->setData(WishlistInterface::SHARING_CODE, $sharingCode);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->_getData(WishlistInterface::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(string $updatedAt)
    {
        $this->setData(WishlistInterface::UPDATED_AT, $updatedAt);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        $name = $this->_getData(WishlistInterface::NAME);
        if ($name === null) {
            $name = __('Wish List')->render();
            $this->setName($name);
        }

        return $name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(WishlistInterface::NAME, $name);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return (int) $this->_getData(WishlistInterface::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->setData(WishlistInterface::TYPE, $type);

        return $this;
    }
}
