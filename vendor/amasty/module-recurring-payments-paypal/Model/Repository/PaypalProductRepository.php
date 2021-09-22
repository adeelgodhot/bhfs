<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


namespace Amasty\RecurringPaypal\Model\Repository;

use Amasty\RecurringPaypal\Api\Data\ProductInterface;
use Amasty\RecurringPaypal\Api\ProductRepositoryInterface;
use Amasty\RecurringPaypal\Model\PaypalProductFactory;
use Amasty\RecurringPaypal\Model\ResourceModel\PaypalProduct as PaypalProductResource;
use Amasty\RecurringPaypal\Model\ResourceModel\PaypalProduct\CollectionFactory;
use Amasty\RecurringPaypal\Model\ResourceModel\PaypalProduct\Collection;
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
class PaypalProductRepository implements ProductRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var PaypalProductFactory
     */
    private $paypalProductFactory;

    /**
     * @var PaypalProductResource
     */
    private $paypalProductResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $paypalProducts;

    /**
     * @var CollectionFactory
     */
    private $paypalProductCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        PaypalProductFactory $paypalProductFactory,
        PaypalProductResource $paypalProductResource,
        CollectionFactory $paypalProductCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->paypalProductFactory = $paypalProductFactory;
        $this->paypalProductResource = $paypalProductResource;
        $this->paypalProductCollectionFactory = $paypalProductCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(ProductInterface $paypalProduct)
    {
        try {
            if ($paypalProduct->getEntityId()) {
                $paypalProduct = $this->getById($paypalProduct->getEntityId())->addData($paypalProduct->getData());
            }
            $this->paypalProductResource->save($paypalProduct);
            unset($this->paypalProducts[$paypalProduct->getEntityId()]);
        } catch (\Exception $e) {
            if ($paypalProduct->getEntityId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save paypalProduct with ID %1. Error: %2',
                        [$paypalProduct->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new paypalProduct. Error: %1', $e->getMessage()));
        }

        return $paypalProduct;
    }

    /**
     * @inheritdoc
     */
    public function getById($entityId)
    {
        if (!isset($this->paypalProducts[$entityId])) {
            $paypalProduct = $this->paypalProductFactory->create();
            $this->paypalProductResource->load($paypalProduct, $entityId);
            if (!$paypalProduct->getEntityId()) {
                throw new NoSuchEntityException(__('Paypal Product with specified ID "%1" not found.', $entityId));
            }
            $this->paypalProducts[$entityId] = $paypalProduct;
        }

        return $this->paypalProducts[$entityId];
    }

    /**
     * @inheritdoc
     */
    public function getByProductId($productId)
    {
        /** @var \Amasty\RecurringPaypal\Model\ResourceModel\PaypalProduct\Collection $productCollection */
        $productCollection = $this->paypalProductCollectionFactory->create();
        $productCollection->addFieldToFilter(ProductInterface::PRODUCT_ID, $productId);
        $paypalProduct = $productCollection->getFirstItem();

        if (!$paypalProduct->getEntityId()) {
            throw new NoSuchEntityException(__('Paypal Product with specified ID "%1" not found.', $productId));
        }

        return $paypalProduct;
    }

    /**
     * @inheritdoc
     */
    public function getByPaypalProductId($paypalProdId)
    {
        /** @var \Amasty\RecurringPaypal\Model\PaypalProduct $paypalProduct */
        $paypalProduct = $this->paypalProductFactory->create();
        $this->paypalProductResource->load($paypalProduct, $paypalProdId, ProductInterface::PAYPAL_PRODUCT_ID);

        if (!$paypalProduct->getEntityId()) {
            throw new NoSuchEntityException(__('Paypal Product with specified ID "%1" not found.', $paypalProdId));
        }

        return $paypalProduct;
    }

    /**
     * @inheritdoc
     */
    public function delete(ProductInterface $paypalProduct)
    {
        try {
            $this->paypalProductResource->delete($paypalProduct);
            unset($this->paypalProducts[$paypalProduct->getEntityId()]);
        } catch (\Exception $e) {
            if ($paypalProduct->getEntityId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove paypalProduct with ID %1. Error: %2',
                        [$paypalProduct->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove paypalProduct. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($entityId)
    {
        $paypalProductModel = $this->getById($entityId);
        $this->delete($paypalProductModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\RecurringPaypal\Model\ResourceModel\PaypalProduct\Collection $paypalProductCollection */
        $paypalProductCollection = $this->paypalProductCollectionFactory->create();

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $paypalProductCollection);
        }

        $searchResults->setTotalCount($paypalProductCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();

        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $paypalProductCollection);
        }

        $paypalProductCollection->setCurPage($searchCriteria->getCurrentPage());
        $paypalProductCollection->setPageSize($searchCriteria->getPageSize());

        $paypalProducts = [];
        /** @var ProductInterface $paypalProduct */
        foreach ($paypalProductCollection->getItems() as $paypalProduct) {
            $paypalProducts[] = $this->getById($paypalProduct->getEntityId());
        }

        $searchResults->setItems($paypalProducts);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $paypalProductCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $paypalProductCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ?: 'eq';
            $paypalProductCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection  $paypalProductCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $paypalProductCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $paypalProductCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
