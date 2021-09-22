<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Api;

use Amasty\RecurringPayments\Api\Data\FeeInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface FeeRepositoryInterface
{
    /**
     * Save
     *
     * @param FeeInterface $fee
     *
     * @return FeeInterface
     */
    public function save(FeeInterface $fee): FeeInterface;

    /**
     * @param int|null $quoteId
     *
     * @return FeeInterface
     */
    public function getByQuoteId($quoteId = null): FeeInterface;

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return FeeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $entityId): FeeInterface;

    /**
     * Delete
     *
     * @param FeeInterface $fee
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(FeeInterface $fee): bool;

    /**
     * Delete by id
     *
     * @param int $entityId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $entityId): bool;

    /**
     * Lists
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;
}
