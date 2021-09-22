<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Repository;

use Amasty\RecurringPayments\Api\Data\FeeInterface;
use Amasty\RecurringPayments\Api\FeeRepositoryInterface;
use Amasty\RecurringPayments\Model\FeeFactory;
use Amasty\RecurringPayments\Model\ResourceModel\Fee as FeeResource;
use Amasty\RecurringPayments\Model\ResourceModel\Fee\CollectionFactory;
use Amasty\RecurringPayments\Model\ResourceModel\Fee\Collection;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * Class FeeRepository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FeeRepository implements FeeRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var FeeFactory
     */
    private $feeFactory;

    /**
     * @var FeeResource
     */
    private $feeResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $fees;

    /**
     * @var CollectionFactory
     */
    private $feeCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        FeeFactory $feeFactory,
        FeeResource $feeResource,
        CollectionFactory $feeCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->feeFactory = $feeFactory;
        $this->feeResource = $feeResource;
        $this->feeCollectionFactory = $feeCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(FeeInterface $fee): FeeInterface
    {
        try {
            if ($fee->getEntityId()) {
                $fee = $this->getById((int)$fee->getEntityId())->addData($fee->getData());
            }
            $this->feeResource->save($fee);
            unset($this->fees[$fee->getEntityId()]);
        } catch (\Exception $e) {
            if ($fee->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save fee with ID %1. Error: %2',
                        [$fee->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new fee. Error: %1', $e->getMessage()));
        }

        return $fee;
    }

    /**
     * @inheritdoc
     */
    public function getByQuoteId($quoteId = null): FeeInterface
    {
        /** @var \Amasty\RecurringPayments\Model\Fee $fee */
        $fee = $this->feeFactory->create();
        $this->feeResource->load($fee, $quoteId, FeeInterface::QUOTE_ID);

        return $fee;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $entityId): FeeInterface
    {
        if (!isset($this->fees[$entityId])) {
            /** @var \Amasty\RecurringPayments\Model\Fee $fee */
            $fee = $this->feeFactory->create();
            $this->feeResource->load($fee, $entityId);
            if (!$fee->getEntityId()) {
                throw new NoSuchEntityException(__('Fee with specified ID "%1" not found.', $entityId));
            }
            $this->fees[$entityId] = $fee;
        }

        return $this->fees[$entityId];
    }

    /**
     * @inheritdoc
     */
    public function delete(FeeInterface $fee): bool
    {
        try {
            $this->feeResource->delete($fee);
            unset($this->fees[$fee->getEntityId()]);
        } catch (\Exception $e) {
            if ($fee->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove fee with ID %1. Error: %2',
                        [$fee->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove fee. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $entityId): bool
    {
        $feeModel = $this->getById($entityId);
        $this->delete($feeModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\RecurringPayments\Model\ResourceModel\Fee\Collection $feeCollection */
        $feeCollection = $this->feeCollectionFactory->create();
        
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $feeCollection);
        }
        
        $searchResults->setTotalCount($feeCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $feeCollection);
        }
        
        $feeCollection->setCurPage($searchCriteria->getCurrentPage());
        $feeCollection->setPageSize($searchCriteria->getPageSize());
        
        $fees = [];
        /** @var FeeInterface $fee */
        foreach ($feeCollection->getItems() as $fee) {
            $fees[] = $this->getById($fee->getEntityId());
        }
        
        $searchResults->setItems($fees);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $feeCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $feeCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $feeCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
    * Helper function that adds a SortOrder to the collection.
    *
    * @param SortOrder[] $sortOrders
    * @param Collection  $feeCollection
    *
    * @return void
    */
    private function addOrderToCollection($sortOrders, Collection $feeCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $feeCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
