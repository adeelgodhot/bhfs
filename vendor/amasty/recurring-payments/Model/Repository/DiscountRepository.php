<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Repository;

use Amasty\RecurringPayments\Api\Data\DiscountInterface;
use Amasty\RecurringPayments\Api\DiscountRepositoryInterface;
use Amasty\RecurringPayments\Model\DiscountFactory;
use Amasty\RecurringPayments\Model\ResourceModel\Discount as DiscountResource;
use Amasty\RecurringPayments\Model\ResourceModel\Discount\CollectionFactory;
use Amasty\RecurringPayments\Model\ResourceModel\Discount\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated  use SubscriptionInterface
 */
class DiscountRepository implements DiscountRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var DiscountFactory
     */
    private $discountFactory;

    /**
     * @var DiscountResource
     */
    private $discountResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $discounts;

    /**
     * @var CollectionFactory
     */
    private $discountCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        DiscountFactory $discountFactory,
        DiscountResource $discountResource,
        CollectionFactory $discountCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->discountFactory = $discountFactory;
        $this->discountResource = $discountResource;
        $this->discountCollectionFactory = $discountCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(DiscountInterface $discount)
    {
        try {
            if ($discount->getEntityId()) {
                $discount = $this->getById($discount->getEntityId())->addData($discount->getData());
            }
            $this->discountResource->save($discount);
            unset($this->discounts[$discount->getEntityId()]);
        } catch (\Exception $e) {
            if ($discount->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save discount with ID %1. Error: %2',
                        [$discount->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new discount. Error: %1', $e->getMessage()));
        }

        return $discount;
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId)
    {
        if (!isset($this->discounts[$entityId])) {
            /** @var \Amasty\RecurringPayments\Model\Discount $discount */
            $discount = $this->discountFactory->create();
            $this->discountResource->load($discount, $entityId);
            if (!$discount->getEntityId()) {
                throw new NoSuchEntityException(__('Discount with specified ID "%1" not found.', $entityId));
            }
            $this->discounts[$entityId] = $discount;
        }

        return $this->discounts[$entityId];
    }

    /**
     * @inheritdoc
     */
    public function getBySubscriptionId($subscriptionId)
    {
        /** @var \Amasty\RecurringPayments\Model\Discount $discount */
        $discount = $this->discountFactory->create();
        $this->discountResource->load($discount, $subscriptionId, DiscountInterface::SUBSCRIPTION_ID);
        if (!$discount->getEntityId()) {
            throw new NoSuchEntityException(__('Discount with specified ID "%1" not found.', $subscriptionId));
        }

        return $discount;
    }

    /**
     * @inheritdoc
     */
    public function delete(DiscountInterface $discount)
    {
        try {
            $this->discountResource->delete($discount);
            unset($this->discounts[$discount->getEntityId()]);
        } catch (\Exception $e) {
            if ($discount->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove discount with ID %1. Error: %2',
                        [$discount->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove discount. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($entityId)
    {
        $discountModel = $this->getById($entityId);
        $this->delete($discountModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\RecurringPayments\Model\ResourceModel\Discount\Collection $discountCollection */
        $discountCollection = $this->discountCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $discountCollection);
        }

        $searchResults->setTotalCount($discountCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $discountCollection);
        }

        $discountCollection->setCurPage($searchCriteria->getCurrentPage());
        $discountCollection->setPageSize($searchCriteria->getPageSize());

        $discounts = [];
        /** @var DiscountInterface $discount */
        foreach ($discountCollection->getItems() as $discount) {
            $discounts[] = $this->getById($discount->getEntityId());
        }

        $searchResults->setItems($discounts);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $discountCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $discountCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $discountCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $discountCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $discountCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $discountCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
