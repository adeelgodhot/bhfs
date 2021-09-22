<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Repository;

use Amasty\RecurringPayments\Api\Data\TransactionInterface;
use Amasty\RecurringPayments\Api\TransactionRepositoryInterface;
use Amasty\RecurringPayments\Model\TransactionFactory;
use Amasty\RecurringPayments\Model\ResourceModel\Transaction as TransactionResource;
use Amasty\RecurringPayments\Model\ResourceModel\Transaction\CollectionFactory;
use Amasty\RecurringPayments\Model\ResourceModel\Transaction\Collection;
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
class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var TransactionResource
     */
    private $transactionResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $transactions;

    /**
     * @var CollectionFactory
     */
    private $transactionCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        TransactionFactory $transactionFactory,
        TransactionResource $transactionResource,
        CollectionFactory $transactionCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->transactionFactory = $transactionFactory;
        $this->transactionResource = $transactionResource;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(TransactionInterface $transaction)
    {
        try {
            if ($transaction->getEntityId()) {
                $transaction = $this->getById($transaction->getEntityId())->addData($transaction->getData());
            }
            $this->transactionResource->save($transaction);
            unset($this->transactions[$transaction->getEntityId()]);
        } catch (\Exception $e) {
            if ($transaction->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save transaction with ID %1. Error: %2',
                        [$transaction->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new transaction. Error: %1', $e->getMessage()));
        }

        return $transaction;
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId)
    {
        if (!isset($this->transactions[$entityId])) {
            /** @var \Amasty\RecurringPayments\Model\Transaction $transaction */
            $transaction = $this->transactionFactory->create();
            $this->transactionResource->load($transaction, $entityId);
            if (!$transaction->getEntityId()) {
                throw new NoSuchEntityException(__('Transaction with specified ID "%1" not found.', $entityId));
            }
            $this->transactions[$entityId] = $transaction;
        }

        return $this->transactions[$entityId];
    }

    /**
     * @inheritdoc
     */
    public function delete(TransactionInterface $transaction)
    {
        try {
            $this->transactionResource->delete($transaction);
            unset($this->transactions[$transaction->getEntityId()]);
        } catch (\Exception $e) {
            if ($transaction->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove transaction with ID %1. Error: %2',
                        [$transaction->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove transaction. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($entityId)
    {
        $transactionModel = $this->getById($entityId);
        $this->delete($transactionModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\RecurringPayments\Model\ResourceModel\Transaction\Collection $transactionCollection */
        $transactionCollection = $this->transactionCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $transactionCollection);
        }

        $searchResults->setTotalCount($transactionCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $transactionCollection);
        }

        $transactionCollection->setCurPage($searchCriteria->getCurrentPage());
        $transactionCollection->setPageSize($searchCriteria->getPageSize());

        $transactions = [];
        /** @var TransactionInterface $transaction */
        foreach ($transactionCollection->getItems() as $transaction) {
            $transactions[] = $this->getById($transaction->getEntityId());
        }

        $searchResults->setItems($transactions);

        return $searchResults;
    }

    /**
     * Method returns only last transaction for each subscription
     *
     * @param array $subscriptionIds
     * @return TransactionInterface[]
     */
    public function getLastRelatedTransactions(array $subscriptionIds)
    {
        /** @var \Amasty\RecurringPayments\Model\ResourceModel\Transaction\Collection $transactionCollection */
        $transactionCollection = $this->transactionCollectionFactory->create();

        $select = $transactionCollection->getSelect();
        // join main table again, where billing_date bigger that current row's billing_date
        $select->joinLeft(
            ['t2' => $transactionCollection->getMainTable()],
            'main_table.subscription_id = t2.subscription_id ' .
            'AND main_table.billing_date < t2.billing_date AND t2.status = 1',
            []
        );
        // get rows, where joined row billing date does not exists (the biggest billing_date rows)
        $select->where('t2.billing_date IS NULL AND main_table.subscription_id IN(?)', $subscriptionIds);
        $select->where('main_table.status = 1');

        $items = [];
        /** @var TransactionInterface $transaction */
        foreach ($transactionCollection as $transaction) {
            $items[$transaction->getSubscriptionId()] = $transaction;
        }

        return $items;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $transactionCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $transactionCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $transactionCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $transactionCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $transactionCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $transactionCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
