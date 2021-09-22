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
interface TransactionRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\RecurringPayments\Api\Data\TransactionInterface $transaction
     *
     * @return \Amasty\RecurringPayments\Api\Data\TransactionInterface
     */
    public function save(\Amasty\RecurringPayments\Api\Data\TransactionInterface $transaction);

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return \Amasty\RecurringPayments\Api\Data\TransactionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Delete
     *
     * @param \Amasty\RecurringPayments\Api\Data\TransactionInterface $transaction
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\RecurringPayments\Api\Data\TransactionInterface $transaction);

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
