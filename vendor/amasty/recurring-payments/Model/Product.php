<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Model;

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Amasty\RecurringPayments\Model\Product\Source\AvailableSubscription;
use Amasty\RecurringPayments\Model\Repository\ActiveSubscriptionPlanRepository;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as MagentoProduct;

class Product implements ProductRecurringAttributesInterface
{
    const FREE_SHIPPING = 'free_shipping';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ActiveSubscriptionPlanRepository
     */
    private $activeSubscriptionPlanRepository;

    public function __construct(
        Config $config,
        ProductRepositoryInterface $productRepository,
        ActiveSubscriptionPlanRepository $activeSubscriptionPlanRepository
    ) {
        $this->config = $config;
        $this->productRepository = $productRepository;
        $this->activeSubscriptionPlanRepository = $activeSubscriptionPlanRepository;
    }

    /**
     * @param MagentoProduct $product
     *
     * @return bool
     */
    public function isRecurringEnable(MagentoProduct $product): bool
    {
        /** @var MagentoProduct $product */
        $product = $this->productRepository->getById($product->getId());
        $recurringEnable = $product->getData(self::RECURRING_ENABLE);

        if (!$recurringEnable || $recurringEnable === AvailableSubscription::NO) {
            return false;
        }

        $activePlans = $this->getActiveSubscriptionPlans($product);

        if (count($activePlans) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param MagentoProduct $product
     *
     * @return bool
     */
    public function isSubscriptionOnly(MagentoProduct $product): bool
    {
        /** @var MagentoProduct $product */
        $product = $this->productRepository->getById($product->getId());
        $recurringEnable = $product->getData(self::RECURRING_ENABLE);

        if ($recurringEnable === AvailableSubscription::GLOBAL_SETTING) {
            return (bool)$this->config->isSubscriptionOnly();
        } elseif ($recurringEnable === AvailableSubscription::CUSTOM_SETTING) {
            return (bool)$product->getData(self::SUBSCRIPTION_ONLY);
        }

        return false;
    }

    /**
     * @param MagentoProduct $product
     * @return array
     */
    public function getSubscriptionPlansIds(MagentoProduct $product): array
    {
        /** @var MagentoProduct $product */
        $product = $this->productRepository->getById($product->getId());
        $recurringEnable = $product->getData(self::RECURRING_ENABLE);

        if ($recurringEnable === AvailableSubscription::GLOBAL_SETTING) {
            return $this->config->getSubscriptionPlansIds();
        } elseif ($recurringEnable === AvailableSubscription::CUSTOM_SETTING) {
            $value = $product->getData(self::PLANS);
            $value = explode(',', $value);
            $value = array_filter($value);

            return $value;
        }

        return [];
    }

    /**
     * @param MagentoProduct $product
     * @return \Amasty\RecurringPayments\Api\Data\SubscriptionPlanInterface[]
     */
    public function getActiveSubscriptionPlans(MagentoProduct $product)
    {
        $ids = $this->getSubscriptionPlansIds($product);
        if (empty($ids)) {
            return [];
        }
        return $this->activeSubscriptionPlanRepository->getListActive($ids);
    }

    /**
     * @return bool
     */
    public function isFreeShipping(): bool
    {
        return $this->config->isEnableFreeShipping();
    }
}
