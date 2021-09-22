<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\Processor;

use Amasty\RecurringPayments\Model\SubscriptionManagement;
use Amasty\RecurringPaypal\Api\ProductRepositoryInterface;
use Amasty\RecurringPaypal\Model\Api\Adapter;
use Amasty\RecurringPaypal\Model\PaypalProduct;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use PayPal\Exception\PayPalConnectionException;

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
            $subscription = $this->subscriptionManagement->generateSubscription(
                $order,
                $item
            );

            $productId = $item->getProduct()->getId();

            try {
                /** @var PaypalProduct $product */
                $product = $this->productRepository->getByProductId($productId);
                // Ensure that product also exists on Paypal side
                $this->adapter->getProductDetails($product->getPaypalProductId());
            } catch (NoSuchEntityException $exception) {
                $product = $this->createProduct->execute($item, (int)$productId);
            } catch (PayPalConnectionException $exception) {
                $this->productRepository->delete($product);
                $product = $this->createProduct->execute($item, (int)$productId);
            }

            $planData = $this->createPlan->execute(
                $subscription,
                $item,
                (string)$product->getPaypalProductId(),
                $order
            );

            $subscriptionId = $this->createSubscription->execute(
                $subscription,
                $planData['id'],
                $order
            );
            $subscription->setSubscriptionId($subscriptionId);
            $this->subscriptionManagement->saveSubscription($subscription, $order);
        }
    }
}
