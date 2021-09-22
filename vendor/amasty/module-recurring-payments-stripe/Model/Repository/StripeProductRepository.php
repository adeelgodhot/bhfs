<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


namespace Amasty\RecurringStripe\Model\Repository;

use Amasty\RecurringStripe\Api\Data\ProductInterface;
use Amasty\RecurringStripe\Api\ProductRepositoryInterface;
use Amasty\RecurringStripe\Model\StripeProductFactory;
use Amasty\RecurringStripe\Model\ResourceModel\StripeProduct as StripeProductResource;
use Amasty\RecurringStripe\Model\ResourceModel\StripeProduct\CollectionFactory;
use Amasty\RecurringStripe\Model\ResourceModel\StripeProduct\Collection;
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
class StripeProductRepository implements ProductRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var StripeProductFactory
     */
    private $stripeProductFactory;

    /**
     * @var StripeProductResource
     */
    private $stripeProductResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $stripeProducts;

    /**
     * @var CollectionFactory
     */
    private $stripeProductCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        StripeProductFactory $stripeProductFactory,
        StripeProductResource $stripeProductResource,
        CollectionFactory $stripeProductCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->stripeProductFactory = $stripeProductFactory;
        $this->stripeProductResource = $stripeProductResource;
        $this->stripeProductCollectionFactory = $stripeProductCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(ProductInterface $stripeProduct)
    {
        try {
            if ($stripeProduct->getEntityId()) {
                $stripeProduct = $this->getById($stripeProduct->getEntityId())->addData($stripeProduct->getData());
            }
            $this->stripeProductResource->save($stripeProduct);
            unset($this->stripeProducts[$stripeProduct->getEntityId()]);
        } catch (\Exception $e) {
            if ($stripeProduct->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save stripeProduct with ID %1. Error: %2',
                        [$stripeProduct->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new stripeProduct. Error: %1', $e->getMessage()));
        }

        return $stripeProduct;
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId)
    {
        if (!isset($this->stripeProducts[$entityId])) {
            $stripeProduct = $this->stripeProductFactory->create();
            $this->stripeProductResource->load($stripeProduct, $entityId);
            if (!$stripeProduct->getEntityId()) {
                throw new NoSuchEntityException(__('Stripe Product with specified ID "%1" not found.', $entityId));
            }
            $this->stripeProducts[$entityId] = $stripeProduct;
        }

        return $this->stripeProducts[$entityId];
    }

    /**
     * @inheritdoc
     */
    public function getByProductId($productId, $stripeAccountId)
    {
        /** @var \Amasty\RecurringStripe\Model\ResourceModel\StripeProduct\Collection $productCollection */
        $productCollection = $this->stripeProductCollectionFactory->create();
        $productCollection->addFieldToFilter(ProductInterface::PRODUCT_ID, $productId);
        $productCollection->addFieldToFilter(ProductInterface::STRIPE_ACCOUNT_ID, $stripeAccountId);
        $stripeProduct = $productCollection->getFirstItem();

        if (!$stripeProduct->getEntityId()) {
            throw new NoSuchEntityException(__('Stripe Product with specified ID "%1" not found.', $productId));
        }

        return $stripeProduct;
    }

    /**
     * @inheritdoc
     */
    public function getByStripeProdId($stripeProdId)
    {
        /** @var \Amasty\RecurringStripe\Model\StripeProduct $stripeProduct */
        $stripeProduct = $this->stripeProductFactory->create();
        $this->stripeProductResource->load($stripeProduct, $stripeProdId, ProductInterface::STRIPE_PRODUCT_ID);

        if (!$stripeProduct->getEntityId()) {
            throw new NoSuchEntityException(__('Stripe Product with specified ID "%1" not found.', $stripeProdId));
        }

        return $stripeProduct;
    }

    /**
     * @inheritdoc
     */
    public function delete(ProductInterface $stripeProduct)
    {
        try {
            $this->stripeProductResource->delete($stripeProduct);
            unset($this->stripeProducts[$stripeProduct->getEntityId()]);
        } catch (\Exception $e) {
            if ($stripeProduct->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove stripeProduct with ID %1. Error: %2',
                        [$stripeProduct->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove stripeProduct. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($entityId)
    {
        $stripeProductModel = $this->getById($entityId);
        $this->delete($stripeProductModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\RecurringStripe\Model\ResourceModel\StripeProduct\Collection $stripeProductCollection */
        $stripeProductCollection = $this->stripeProductCollectionFactory->create();
        
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $stripeProductCollection);
        }
        
        $searchResults->setTotalCount($stripeProductCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $stripeProductCollection);
        }
        
        $stripeProductCollection->setCurPage($searchCriteria->getCurrentPage());
        $stripeProductCollection->setPageSize($searchCriteria->getPageSize());
        
        $stripeProducts = [];
        /** @var ProductInterface $stripeProduct */
        foreach ($stripeProductCollection->getItems() as $stripeProduct) {
            $stripeProducts[] = $this->getById($stripeProduct->getEntityId());
        }
        
        $searchResults->setItems($stripeProducts);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $stripeProductCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $stripeProductCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $stripeProductCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $stripeProductCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $stripeProductCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $stripeProductCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
