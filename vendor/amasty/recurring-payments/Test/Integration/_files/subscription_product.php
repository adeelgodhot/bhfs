<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */

include 'subscription_plan.php';

/** @var $product \Magento\Catalog\Model\Product */

use Amasty\RecurringPayments\Api\Data\ProductRecurringAttributesInterface;
use Amasty\RecurringPayments\Model\Product\Source\AvailableSubscription;
use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\Framework\Registry $registry */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$skuAndUrlKey = 'simple_subscriptional_product_sku';
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product ' . $skuAndUrlKey)
    ->setSku($skuAndUrlKey)
    ->setPrice(10)
    ->setDescription('Description with <b>html tag</b>')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setCategoryIds([2])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setUrlKey($skuAndUrlKey);

//@TODO add subscription data;
$product->addData([
    ProductRecurringAttributesInterface::RECURRING_ENABLE => AvailableSubscription::CUSTOM_SETTING,
    ProductRecurringAttributesInterface::PLANS => '77,88'
]);

$product->save();
