<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


namespace Amasty\MWishlist\Api;

/**
 * @api
 */
interface WishlistRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\MWishlist\Api\Data\WishlistInterface $wishlist
     *
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     */
    public function save(\Amasty\MWishlist\Api\Data\WishlistInterface $wishlist);

    /**
     * Get by id
     *
     * @param int $id
     *
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Get by Customer Id
     *
     * @param int $customerId
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCustomerId($customerId);

    /**
     * @return \Amasty\MWishlist\Api\Data\WishlistInterface
     */
    public function create();

    /**
     * Delete
     *
     * @param \Amasty\MWishlist\Api\Data\WishlistInterface $wishlist
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\MWishlist\Api\Data\WishlistInterface $wishlist);

    /**
     * Delete by id
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $customerId
     * @param null $type
     * @return \Amasty\MWishlist\Model\ResourceModel\Wishlist\Collection
     */
    public function getWishlistsByCustomerId(int $customerId, $type = null);

    /**
     * @param int $customerId
     * @param string $wishlistName
     * @return bool
     */
    public function isWishlistExist(int $customerId, string $wishlistName);
}
