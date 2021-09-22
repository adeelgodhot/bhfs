<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Api;

/**
 * @api
 * @deprecated  use SubscriptionInterface
 */
interface DiscountRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\RecurringPayments\Api\Data\DiscountInterface $discount
     *
     * @return \Amasty\RecurringPayments\Api\Data\DiscountInterface
     */
    public function save(\Amasty\RecurringPayments\Api\Data\DiscountInterface $discount);

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return \Amasty\RecurringPayments\Api\Data\DiscountInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Get by id
     *
     * @param string $subscriptionId
     *
     * @return \Amasty\RecurringPayments\Api\Data\DiscountInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBySubscriptionId($subscriptionId);

    /**
     * Delete
     *
     * @param \Amasty\RecurringPayments\Api\Data\DiscountInterface $discount
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\RecurringPayments\Api\Data\DiscountInterface $discount);

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
