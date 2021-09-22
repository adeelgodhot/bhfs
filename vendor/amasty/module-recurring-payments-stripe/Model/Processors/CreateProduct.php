<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Model\Processors;

use Amasty\RecurringStripe\Api\Data\ProductInterface;
use Amasty\RecurringStripe\Model\Adapter;
use Amasty\RecurringStripe\Api\ProductRepositoryInterface;
use Amasty\RecurringStripe\Model\StripeProduct;
use Amasty\RecurringStripe\Model\StripeProductFactory;
use Magento\Quote\Api\Data\CartItemInterface;

class CreateProduct extends AbstractProcessor
{
    /**
     * @var StripeProductFactory
     */
    private $stripeProductFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Adapter $adapter,
        StripeProductFactory $stripeProductFactory,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($adapter);
        $this->stripeProductFactory = $stripeProductFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * @param CartItemInterface $item
     * @param int $productId
     *
     * @return ProductInterface
     */
    public function execute(CartItemInterface $item, int $productId): ProductInterface
    {
        $params = [
            'name' => $item->getName(),
            'type' => 'service',
        ];

        /** @var \Stripe\Product $product */
        $product = $this->adapter->productCreate($params);

        /** @var StripeProduct $stripeProduct */
        $stripeProduct = $this->stripeProductFactory->create();
        $stripeProduct->setProductId($productId);
        $stripeProduct->setStripeProductId($product->id);
        $stripeProduct->setStripeAccountId($this->adapter->getAccountId());

        return $this->productRepository->save($stripeProduct);
    }
}
