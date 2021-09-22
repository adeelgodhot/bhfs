<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Model\Repository;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Api\LabelRepositoryInterface;
use Amasty\Label\Model\Label;
use Amasty\Label\Model\LabelFactory as LabelsFactory;
use Amasty\Label\Model\ResourceModel\Label as LabelsResource;
use Amasty\Label\Model\ResourceModel\Label\Collection;
use Amasty\Label\Model\ResourceModel\Label\CollectionFactory;
use Amasty\Label\Model\ResourceModel\Label\GetRelatedEntitiesIds as GetLabelCustomerGroupIds;
use Amasty\Label\Model\ResourceModel\Label\GetRelatedEntitiesIds as GetLabelStoreIds;
use Amasty\Label\Model\Source\Status;
use Amasty\Label\Ui\DataProvider\Label\Modifiers\Form\AddCustomerGroupsData;
use Amasty\Label\Ui\DataProvider\Label\Modifiers\Form\AddStoresData;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LabelsRepository implements LabelRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var LabelsFactory
     */
    private $labelsFactory;

    /**
     * @var LabelsResource
     */
    private $labelsResource;

    /**
     * @var GetLabelStoreIds
     */
    private $getLabelStoreIds;

    /**
     * @var GetLabelStoreIds
     */
    private $getLabelCustomerGroupIds;

    /**
     * @var CollectionFactory
     */
    private $labelsCollectionFactory;

    /**
     * @var LabelInterface[]
     */
    private $labelCache = [];

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        LabelsFactory $labelsFactory,
        LabelsResource $labelsResource,
        CollectionFactory $labelsCollectionFactory,
        GetLabelStoreIds $getLabelStoreIds,
        GetLabelCustomerGroupIds $getLabelCustomerGroupIds
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->labelsFactory = $labelsFactory;
        $this->labelsResource = $labelsResource;
        $this->labelsCollectionFactory = $labelsCollectionFactory;
        $this->getLabelStoreIds = $getLabelStoreIds;
        $this->getLabelCustomerGroupIds = $getLabelCustomerGroupIds;
    }

    /**
     * @param Label $label
     * @return LabelInterface
     * @throws CouldNotSaveException
     */
    public function save(LabelInterface $label): LabelInterface
    {
        try {
            if ($label->getId()) {
                $persistedLabel = $this->getById($label->getLabelId());
                $label = $persistedLabel->setData($label->getData());
            }

            $this->labelsResource->save($label);
            unset($this->labelCache[$label->getId()]);
        } catch (\Exception $e) {
            if ($label->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save label with ID %1. Error: %2',
                        [$label->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new labels. Error: %1', $e->getMessage()));
        }

        return $label;
    }

    public function getById(int $id, int $mode = Collection::MODE_PDP)
    {
        if (!isset($this->labelCache[$id][$mode])) {
            $collection = $this->labelsCollectionFactory->create();
            $collection->addFieldToFilter(LabelInterface::LABEL_ID, $id);
            $collection->setMode($mode);
            $label = $collection->getFirstItem();

            if (!$label->getId()) {
                throw new NoSuchEntityException(__('Labels with specified ID "%1" not found.', $id));
            }
            $label->setData(AddStoresData::DATA_SCOPE, $this->getLabelStoreIds->execute($label->getLabelId()));
            $label->setData(
                AddCustomerGroupsData::DATA_SCOPE,
                $this->getLabelCustomerGroupIds->execute($label->getLabelId())
            );
            $this->labelCache[$id][$mode] = $label;
        }

        return $this->labelCache[$id][$mode];
    }

    public function delete(LabelInterface $label): bool
    {
        try {
            $this->labelsResource->delete($label);
            unset($this->labelCache[$label->getId()]);
        } catch (\Exception $e) {
            if ($label->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove labels with ID %1. Error: %2',
                        [$label->getId(), $e->getMessage()]
                    )
                );
            }

            throw new CouldNotDeleteException(__('Unable to remove labels. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        $labelsModel = $this->getById($id);
        $this->delete($labelsModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Label\Model\ResourceModel\Label\Collection $labelsCollection */
        $labelsCollection = $this->labelsCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $labelsCollection);
        }
        $searchResults->setTotalCount($labelsCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $labelsCollection);
        }
        $labelsCollection->setCurPage($searchCriteria->getCurrentPage());
        $labelsCollection->setPageSize($searchCriteria->getPageSize());
        $labelss = [];
        /** @var LabelInterface $labels */
        foreach ($labelsCollection->getItems() as $labels) {
            $labelss[] = $this->getById($labels->getId());
        }
        $searchResults->setItems($labelss);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection  $labelsCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $labelsCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $labelsCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $labelsCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $labelsCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $labelsCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }

    public function duplicateLabel(int $id): void
    {
        $model = $this->getById($id);
        $model->setId(null);
        $model->setStatus(Status::INACTIVE);
        $this->save($model);
        $catalogPartModel = $this->getById($id, Collection::MODE_LIST);
        $catalogPartModel->setLabelId($model->getLabelId());
        $catalogPartModel->setStatus(Status::INACTIVE);
        $this->save($catalogPartModel);
    }

    public function getAll(int $mode = Collection::MODE_PDP): array
    {
        $labelCollection = $this->labelsCollectionFactory->create();
        $labelCollection->setMode($mode);

        return $labelCollection->getItems();
    }

    /**
     * @return LabelInterface
     */
    public function getModelLabel(): LabelInterface
    {
        return $this->labelsFactory->create();
    }
}
