<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api;

/**
 * @api
 */
interface SubscriptionPlanRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface $subscriptionPlan
     * @return \Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface
     */
    public function save(\Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface $subscriptionPlan);

    /**
     * Get by id
     *
     * @param int $planId
     * @return \Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $planId);

    /**
     * Delete
     *
     * @param \Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface $subscriptionPlan
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface $subscriptionPlan);

    /**
     * Delete by id
     *
     * @param int $planId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $planId);

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
