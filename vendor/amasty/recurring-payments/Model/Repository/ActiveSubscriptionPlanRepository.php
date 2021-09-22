<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Repository;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Api\SubscriptionPlanRepositoryInterface;
use Amasty\RecurringPayments\Model\Config\Source\PlanStatus;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class ActiveSubscriptionPlanRepository
{
    private $cache = [];

    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $subscriptionPlanRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    public function __construct(
        SubscriptionPlanRepositoryInterface $subscriptionPlanRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->subscriptionPlanRepository = $subscriptionPlanRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * @param array $ids
     * @return SubscriptionPlanInterface[]
     */
    public function getListActive(array $ids)
    {
        $cacheKey = $this->getCacheKey($ids);

        if (!array_key_exists($cacheKey, $this->cache)) {
            $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
            $searchCriteriaBuilder->addFilter(
                SubscriptionPlanInterface::PLAN_ID,
                $ids,
                'in'
            );
            $searchCriteriaBuilder->addFilter(SubscriptionPlanInterface::STATUS, PlanStatus::ACTIVE);

            $this->cache[$cacheKey] = $this->subscriptionPlanRepository
                ->getList($searchCriteriaBuilder->create())
                ->getItems();
        }

        return $this->cache[$cacheKey];
    }

    /**
     * @param array $ids
     * @return string
     */
    private function getCacheKey(array $ids)
    {
        sort($ids);

        return implode(',', $ids);
    }
}
