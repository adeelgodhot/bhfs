<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api\Subscription;

/**
 * @api
 */
interface RepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface $subscription
     *
     * @return \Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface
     */
    public function save(\Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface $subscription);

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return \Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Get by id
     *
     * @param string $subscriptionId
     *
     * @return \Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBySubscriptionId($subscriptionId);

    /**
     * Delete
     *
     * @param \Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface $subscription
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\RecurringPayments\Api\Subscription\SubscriptionInterface $subscription);

    /**
     * Delete by id
     *
     * @param int $entityId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($entityId);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
