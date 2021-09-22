<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model;

use Amasty\RecurringPayments\Model\SubscriptionManagement;
use Amasty\RecurringStripe\Api\ProductRepositoryInterface;
use Amasty\RecurringStripe\Model\Processors\CreatePlan;
use Amasty\RecurringStripe\Model\Processors\CreateProduct;
use Amasty\RecurringStripe\Model\Processors\CreateSubscription;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

class Processor
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CreateProduct
     */
    private $createProduct;

    /**
     * @var CreatePlan
     */
    private $createPlan;

    /**
     * @var CreateSubscription
     */
    private $createSubscription;

    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var SubscriptionManagement
     */
    private $subscriptionManagement;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        CreateProduct $createProduct,
        CreatePlan $createPlan,
        CreateSubscription $createSubscription,
        Adapter $adapter,
        SubscriptionManagement $subscriptionManagement
    ) {
        $this->productRepository = $productRepository;
        $this->createProduct = $createProduct;
        $this->createPlan = $createPlan;
        $this->createSubscription = $createSubscription;
        $this->adapter = $adapter;
        $this->subscriptionManagement = $subscriptionManagement;
    }

    /**
     * @param OrderInterface $order
     * @param \Magento\Quote\Model\Quote\Item[] $items
     */
    public function process(OrderInterface $order, array $items)
    {
        foreach ($items as $item) {
            $subscription = $this->subscriptionManagement->generateSubscription($order, $item);
            $productId = $item->getProduct()->getId();

            try {
                /** @var StripeProduct $product */
                $product = $this->productRepository->getByProductId($productId, $this->adapter->getAccountId());
            } catch (NoSuchEntityException $exception) {
                $product = $this->createProduct->execute($item, (int)$productId);
            }

            /** @var \Stripe\Plan $plan */
            $plan = $this->createPlan->execute($subscription, $item, (string)$product->getStripeProductId());
            $subscriptionId = $this->createSubscription->execute($subscription, $item, $order, $plan);
            $subscription->setSubscriptionId($subscriptionId);
            $this->subscriptionManagement->saveSubscription($subscription, $order);
        }
    }
}
