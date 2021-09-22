<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Repository;

use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Api\Subscription\RepositoryInterface;
use Amasty\RecurringPayments\Model\SubscriptionFactory;
use Amasty\RecurringPayments\Model\ResourceModel\Subscription as SubscriptionResource;
use Amasty\RecurringPayments\Model\ResourceModel\Subscription\CollectionFactory;
use Amasty\RecurringPayments\Model\ResourceModel\Subscription\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubscriptionRepository implements RepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var SubscriptionFactory
     */
    private $subscriptionFactory;

    /**
     * @var SubscriptionResource
     */
    private $subscriptionResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $subscriptions;

    /**
     * @var CollectionFactory
     */
    private $subscriptionCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        SubscriptionFactory $subscriptionFactory,
        SubscriptionResource $subscriptionResource,
        CollectionFactory $subscriptionCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->subscriptionResource = $subscriptionResource;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(SubscriptionInterface $subscription)
    {
        try {
            if ($subscription->getId()) {
                $subscription = $this->getById($subscription->getId())->addData($subscription->getData());
            }
            $this->subscriptionResource->save($subscription);
            unset($this->subscriptions[$subscription->getId()]);
        } catch (\Exception $e) {
            if ($subscription->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save subscription with ID %1. Error: %2',
                        [$subscription->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new subscription. Error: %1', $e->getMessage()));
        }

        return $subscription;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->subscriptions[$id])) {
            /** @var \Amasty\RecurringPayments\Model\Subscription $subscription */
            $subscription = $this->subscriptionFactory->create();
            $this->subscriptionResource->load($subscription, $id);
            if (!$subscription->getId()) {
                throw new NoSuchEntityException(__('Subscription with specified ID "%1" not found.', $id));
            }
            $this->subscriptions[$id] = $subscription;
        }

        return $this->subscriptions[$id];
    }

    /**
     * @inheritdoc
     */
    public function getBySubscriptionId($subscriptionId)
    {
        /** @var \Amasty\RecurringPayments\Model\Subscription $subscription */
        $subscription = $this->subscriptionFactory->create();
        $this->subscriptionResource->load($subscription, $subscriptionId, SubscriptionInterface::SUBSCRIPTION_ID);
        if (!$subscription->getId()) {
            throw new NoSuchEntityException(__('Subscription with specified ID "%1" not found.', $subscriptionId));
        }

        return $subscription;
    }

    /**
     * @inheritdoc
     */
    public function delete(SubscriptionInterface $subscription)
    {
        try {
            $this->subscriptionResource->delete($subscription);
            unset($this->subscriptions[$subscription->getId()]);
        } catch (\Exception $e) {
            if ($subscription->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove subscription with ID %1. Error: %2',
                        [$subscription->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove subscription. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $subscriptionModel = $this->getById($id);
        $this->delete($subscriptionModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\RecurringPayments\Model\ResourceModel\Subscription\Collection $subscriptionCollection */
        $subscriptionCollection = $this->subscriptionCollectionFactory->create();
        
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $subscriptionCollection);
        }
        
        $searchResults->setTotalCount($subscriptionCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $subscriptionCollection);
        }
        
        $subscriptionCollection->setCurPage($searchCriteria->getCurrentPage());
        $subscriptionCollection->setPageSize($searchCriteria->getPageSize());
        
        $subscriptions = [];
        /** @var SubscriptionInterface $subscription */
        foreach ($subscriptionCollection->getItems() as $subscription) {
            $subscriptions[] = $this->getById($subscription->getId());
        }
        
        $searchResults->setItems($subscriptions);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $subscriptionCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $subscriptionCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $subscriptionCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $subscriptionCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $subscriptionCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $subscriptionCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
