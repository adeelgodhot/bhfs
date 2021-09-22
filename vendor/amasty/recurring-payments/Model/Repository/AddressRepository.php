<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Repository;

use Amasty\RecurringPayments\Api\Subscription\AddressInterface;
use Amasty\RecurringPayments\Api\Subscription\AddressRepositoryInterface;
use Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface;
use Amasty\RecurringPayments\Model\Address;
use Amasty\RecurringPayments\Model\AddressFactory;
use Amasty\RecurringPayments\Model\ResourceModel\Address as AddressResource;
use Amasty\RecurringPayments\Model\ResourceModel\Address\CollectionFactory;
use Amasty\RecurringPayments\Model\ResourceModel\Address\Collection;
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
class AddressRepository implements AddressRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var AddressResource
     */
    private $addressResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $addresss;

    /**
     * @var CollectionFactory
     */
    private $addressCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        AddressFactory $addressFactory,
        AddressResource $addressResource,
        CollectionFactory $addressCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->addressFactory = $addressFactory;
        $this->addressResource = $addressResource;
        $this->addressCollectionFactory = $addressCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(AddressInterface $address)
    {
        try {
            if ($address->getId()) {
                $address = $this->getById($address->getId())->addData($address->getData());
            }
            $this->addressResource->save($address);
            unset($this->addresss[$address->getId()]);
        } catch (\Exception $e) {
            if ($address->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save address with ID %1. Error: %2',
                        [$address->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new address. Error: %1', $e->getMessage()));
        }

        return $address;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->addresss[$id])) {
            /** @var Address $address */
            $address = $this->addressFactory->create();
            $this->addressResource->load($address, $id);
            if (!$address->getId()) {
                throw new NoSuchEntityException(__('Address with specified ID "%1" not found.', $id));
            }
            $this->addresss[$id] = $address;
        }

        return $this->addresss[$id];
    }

    /**
     * @inheritdoc
     */
    public function getBySubscriptionId($subscriptionId)
    {
        /** @var \Amasty\RecurringPayments\Model\Address $address */
        $address = $this->addressFactory->create();
        $this->addressResource->load($address, $subscriptionId, SubscriptionInterface::SUBSCRIPTION_ID);
        if (!$address->getEntityId()) {
            throw new NoSuchEntityException(
                __('Address with specified subscription id "%1" not found.', $subscriptionId)
            );
        }

        return $address;
    }

    /**
     * @inheritdoc
     */
    public function delete(AddressInterface $address)
    {
        try {
            $this->addressResource->delete($address);
            unset($this->addresss[$address->getId()]);
        } catch (\Exception $e) {
            if ($address->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove address with ID %1. Error: %2',
                        [$address->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove address. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $addressModel = $this->getById($id);
        $this->delete($addressModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var Collection $addressCollection */
        $addressCollection = $this->addressCollectionFactory->create();
        
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $addressCollection);
        }
        
        $searchResults->setTotalCount($addressCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $addressCollection);
        }
        
        $addressCollection->setCurPage($searchCriteria->getCurrentPage());
        $addressCollection->setPageSize($searchCriteria->getPageSize());
        
        $addresss = [];
        /** @var AddressInterface $address */
        foreach ($addressCollection->getItems() as $address) {
            $addresss[] = $this->getById($address->getId());
        }
        
        $searchResults->setItems($addresss);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $addressCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $addressCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $addressCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $addressCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $addressCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $addressCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
