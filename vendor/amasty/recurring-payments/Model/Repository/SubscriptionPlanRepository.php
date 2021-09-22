<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model\Repository;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterfaceFactory;
use Amasty\RecurringPayments\Api\SubscriptionPlanRepositoryInterface;
use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan as SubscriptionPlanResource;
use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan\Collection;
use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class SubscriptionPlanRepository implements SubscriptionPlanRepositoryInterface
{
    /**
     * @var SubscriptionPlanResource
     */
    private $subscriptionPlanResource;

    /**
     * @var SubscriptionPlanInterfaceFactory
     */
    private $subscriptionPlanFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var array
     */
    private $cache = [];

    public function __construct(
        SubscriptionPlanResource $subscriptionPlanResource,
        SubscriptionPlanInterfaceFactory $subscriptionPlanFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->subscriptionPlanResource = $subscriptionPlanResource;
        $this->subscriptionPlanFactory = $subscriptionPlanFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save
     *
     * @param \Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface $subscriptionPlan
     * @return \Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface
     * @throws CouldNotSaveException
     */
    public function save(\Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface $subscriptionPlan)
    {
        try {
            $this->subscriptionPlanResource->save($subscriptionPlan);
            unset($this->cache[$subscriptionPlan->getPlanId()]);
        } catch (\Exception $e) {
            if ($subscriptionPlan->getPlanId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save subscription plan with ID %1. Error: %2',
                        [$subscriptionPlan->getPlanId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save subscription plan. Error: %1', $e->getMessage()));
        }

        return $subscriptionPlan;
    }

    /**
     * Get by id
     *
     * @param int $planId
     * @return \Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $planId)
    {
        if (!array_key_exists($planId, $this->cache)) {
            $subscriptionPlan = $this->subscriptionPlanFactory->create();
            $this->subscriptionPlanResource->load($subscriptionPlan, $planId);
            if (!$subscriptionPlan->getPlanId()) {
                throw new NoSuchEntityException(__('Subscription plan with specified ID "%1" not found.', $planId));
            }

            $this->cache[$planId] = $subscriptionPlan;
        }

        return $this->cache[$planId];
    }

    /**
     * Delete
     *
     * @param \Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface $subscriptionPlan
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface $subscriptionPlan)
    {
        try {
            unset($this->cache[$subscriptionPlan->getPlanId()]);
            $this->subscriptionPlanResource->delete($subscriptionPlan);
        } catch (\Exception $e) {
            if ($subscriptionPlan->getPlanId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove subscription plan with ID %1. Error: %2',
                        [$subscriptionPlan->getEntityId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove subscription plan. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * Delete by id
     *
     * @param int $planId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $planId)
    {
        $subscriptionPlan = $this->getById($planId);
        $this->delete($subscriptionPlan);
    }

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var SearchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setItems($collection->getItems());

        return $searchResult;
    }
}
