<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\Quote;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface;
use Amasty\RecurringPayments\Api\SubscriptionPlanRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class ItemDataRetriever
{
    /**
     * @var SubscriptionPlanRepositoryInterface
     */
    private $subscriptionPlanRepository;

    public function __construct(SubscriptionPlanRepositoryInterface $subscriptionPlanRepository)
    {
        $this->subscriptionPlanRepository = $subscriptionPlanRepository;
    }

    /**
     * @param AbstractItem $item
     * @return bool
     */
    public function isSubscription(AbstractItem $item)
    {
        $buyRequest = $this->getBuyRequestObject($item);

        return $buyRequest->getData('subscribe') === 'subscribe';
    }

    /**
     * @param AbstractItem $item
     * @return int|null
     */
    public function getPlanId(AbstractItem $item)
    {
        $buyRequest = $this->getBuyRequestObject($item);

        return $buyRequest->getData(ProductRecurringAttributesInterface::SUBSCRIPTION_PLAN_ID);
    }

    /**
     * @param AbstractItem $item
     * @param bool $silent
     * @return SubscriptionPlanInterface|null
     * @throws NoSuchEntityException
     */
    public function getPlan(AbstractItem $item, $silent = true)
    {
        $planId = $this->getPlanId($item);

        try {
            $plan = $this->subscriptionPlanRepository->getById($planId);
        } catch (NoSuchEntityException $e) {
            if (!$silent) {
                throw $e;
            }
            return null;
        }

        return $plan;
    }

    /**
     * @param AbstractItem $item
     * @return DataObject
     */
    private function getBuyRequestObject(AbstractItem $item)
    {
        /** @var DataObject $request */
        $request = $item->getBuyRequest();
        if (!$request && $item->getQuoteItem()) {
            $request = $item->getQuoteItem()->getBuyRequest();
        }
        if (!$request) {
            $request = new DataObject();
        }

        if (is_array($request)) {
            $request = new DataObject($request);
        }

        return $request;
    }
}
