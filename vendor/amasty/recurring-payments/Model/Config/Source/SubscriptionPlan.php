<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Config\Source;

use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Model\AbstractArray;
use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan\Collection;
use Amasty\RecurringPayments\Model\ResourceModel\SubscriptionPlan\CollectionFactory;

class SubscriptionPlan extends AbstractArray
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $plans;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        if ($this->plans === null) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(
                SubscriptionPlanInterface::STATUS,
                PlanStatus::ACTIVE
            );
            $collection->addOrder(
                SubscriptionPlanInterface::PLAN_ID,
                \Magento\Framework\Data\Collection::SORT_ORDER_ASC
            );

            $plans = [];
            foreach ($collection as $plan) {
                $plans[$plan->getPlanId()] = $plan->getName();
            }

            $this->plans = $plans;
        }

        return $this->plans;
    }
}
