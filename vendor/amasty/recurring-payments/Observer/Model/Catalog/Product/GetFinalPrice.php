<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Observer\Model\Catalog\Product;

use Amasty\RecurringPayments\Model\Generators\QuoteGenerator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class GetFinalPrice implements ObserverInterface
{
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $quote */
        $product = $observer->getData('product');

        if ($product->getData(QuoteGenerator::SUBSCRIPTION_FLAG)) {
            $product->setFinalPrice($product->getPrice());
        }
    }
}
