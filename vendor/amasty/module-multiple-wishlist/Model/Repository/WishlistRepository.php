<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


namespace Amasty\MWishlist\Model\Repository;

use Amasty\MWishlist\Api\Data\WishlistInterface;
use Amasty\MWishlist\Api\WishlistRepositoryInterface;
use Amasty\MWishlist\Model\WishlistFactory;
use Amasty\MWishlist\Model\ResourceModel\Wishlist as WishlistResource;
use Amasty\MWishlist\Model\ResourceModel\Wishlist\Collection;
use Amasty\MWishlist\Model\ResourceModel\Wishlist\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WishlistRepository implements WishlistRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;

    /**
     * @var WishlistResource
     */
    private $wishlistResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $wishlists;

    /**
     * @var CollectionFactory
     */
    private $wishlistCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        WishlistFactory $wishlistFactory,
        WishlistResource $wishlistResource,
        CollectionFactory $wishlistCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistResource = $wishlistResource;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(WishlistInterface $wishlist)
    {
        try {
            if ($wishlist->getWishlistId()) {
                $wishlist = $this->getById($wishlist->getWishlistId())->addData($wishlist->getData());
            }
            $this->wishlistResource->save($wishlist);
            unset($this->wishlists[$wishlist->getWishlistId()]);
        } catch (\Exception $e) {
            if ($wishlist->getWishlistId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save wishlist with ID %1. Error: %2',
                        [$wishlist->getWishlistId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new wishlist. Error: %1', $e->getMessage()));
        }

        return $wishlist;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->wishlists[$id])) {
            /** @var \Amasty\MWishlist\Model\Wishlist $wishlist */
            $wishlist = $this->wishlistFactory->create();
            $this->wishlistResource->load($wishlist, $id);
            if (!$wishlist->getWishlistId()) {
                throw new NoSuchEntityException(__('Wishlist with specified ID "%1" not found.', $id));
            }
            $this->wishlists[$id] = $wishlist;
        }

        return $this->wishlists[$id];
    }

    /**
     * @inheritdoc
     */
    public function getByCustomerId($customerId)
    {
        /** @var \Amasty\MWishlist\Model\Wishlist $wishlist */
        $wishlist = $this->wishlistFactory->create();
        $wishlist->loadByCustomerId($customerId);
        if (!$wishlist->getWishlistId()) {
            throw new NoSuchEntityException(__('Wishlist with specified Customer ID "%1" not found.', $customerId));
        }
        $this->wishlists[$wishlist->getId()] = $wishlist;

        return $this->wishlists[$wishlist->getId()];
    }

    /**
     * @inheritdoc
     */
    public function create()
    {
        return $this->wishlistFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function delete(WishlistInterface $wishlist)
    {
        try {
            $this->wishlistResource->delete($wishlist);
            unset($this->wishlists[$wishlist->getWishlistId()]);
        } catch (\Exception $e) {
            if ($wishlist->getWishlistId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove wishlist with ID %1. Error: %2',
                        [$wishlist->getWishlistId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove wishlist. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $wishlistModel = $this->getById($id);
        $this->delete($wishlistModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\MWishlist\Model\ResourceModel\Wishlist\Collection $wishlistCollection */
        $wishlistCollection = $this->wishlistCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $wishlistCollection);
        }

        $searchResults->setTotalCount($wishlistCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $wishlistCollection);
        }

        $wishlistCollection->setCurPage($searchCriteria->getCurrentPage());
        $wishlistCollection->setPageSize($searchCriteria->getPageSize());

        $wishlists = [];
        /** @var WishlistInterface $wishlist */
        foreach ($wishlistCollection->getItems() as $wishlist) {
            $wishlists[] = $this->getById($wishlist->getWishlistId());
        }

        $searchResults->setItems($wishlists);

        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getWishlistsByCustomerId(int $customerId, $type = null)
    {
        /** @var Collection $wishlistCollection */
        $wishlistCollection = $this->wishlistCollectionFactory->create();
        $wishlistCollection->filterByCustomerId($customerId);
        if ($type !== null) {
            $wishlistCollection->filterByType($type);
        }
        $wishlistCollection->orderByDate();

        return $wishlistCollection;
    }

    /**
     * @inheritdoc
     */
    public function isWishlistExist(int $customerId, string $wishlistName)
    {
        return (bool) $this->getWishlistsByCustomerId($customerId)->filterByName($wishlistName)->getSize();
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $wishlistCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $wishlistCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $wishlistCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $wishlistCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $wishlistCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $wishlistCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
