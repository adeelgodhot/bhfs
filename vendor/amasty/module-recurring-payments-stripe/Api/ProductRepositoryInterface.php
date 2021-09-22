<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Api;

use Amasty\RecurringStripe\Api\Data\ProductInterface as ProductInterface;

/**
 * @api
 */
interface ProductRepositoryInterface
{
    /**
     * Save
     *
     * @param ProductInterface $product
     *
     * @return ProductInterface
     */
    public function save(ProductInterface $product);

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId);

    /**
     * Get by id
     *
     * @param int $productId
     * @param string $stripeAccountId
     *
     * @return ProductInterface
     */
    public function getByProductId($productId, $stripeAccountId);

    /**
     * Get by stripe product id
     *
     * @param string $stripeProdId
     *
     * @return ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByStripeProdId($stripeProdId);

    /**
     * Delete
     *
     * @param ProductInterface $product
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(ProductInterface $product);

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
