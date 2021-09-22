<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */

declare(strict_types=1);

namespace Amasty\Paction\Model;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class GetProductCollectionByIds
{
    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var EntityResolver
     */
    private $entityResolver;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    public function __construct(
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        EntityResolver $entityResolver,
        ProductRepository $productRepository
    ) {
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->entityResolver = $entityResolver;
        $this->productRepository = $productRepository;
    }

    public function get(array $ids, string $entityField = 'entity_id')
    {
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder */
        $criteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $criteriaBuilder->addFilter(
            $entityField,
            $ids,
            'IN'
        )->create();

        return $this->productRepository->getList($searchCriteria)->getItems();
    }
}
