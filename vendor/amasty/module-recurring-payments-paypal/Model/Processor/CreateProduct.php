<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


declare(strict_types=1);

namespace Amasty\RecurringPaypal\Model\Processor;

use Amasty\RecurringPaypal\Api\Data\ProductInterface;
use Amasty\RecurringPaypal\Api\ProductRepositoryInterface;
use Amasty\RecurringPaypal\Model\Api\Adapter;
use Amasty\RecurringPaypal\Model\PaypalProduct;
use Amasty\RecurringPaypal\Model\PaypalProductFactory;
use Magento\Quote\Api\Data\CartItemInterface;

class CreateProduct extends AbstractProcessor
{
    const DEFAULT_PRODUCT_TYPE = 'SERVICE';

    /**
     * @var PaypalProductFactory
     */
    private $paypalProductFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Adapter $adapter,
        PaypalProductFactory $paypalProductFactory,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($adapter);
        $this->paypalProductFactory = $paypalProductFactory;
        $this->productRepository = $productRepository;
    }

    public function execute(CartItemInterface $item, int $productId): ProductInterface
    {
        $productData = $this->adapter->createProduct([
            'name' => $item->getName(),
            'type' => self::DEFAULT_PRODUCT_TYPE
        ]);

        /** @var PaypalProduct $paypalProduct */
        $paypalProduct = $this->paypalProductFactory->create();
        $paypalProduct->setProductId($productId);
        $paypalProduct->setPaypalProductId($productData['id']);

        return $this->productRepository->save($paypalProduct);
    }
}
