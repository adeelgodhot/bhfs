<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Plugin\Observer;

use Amasty\RecurringPayments\Model\Generators\QuoteGenerator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProcessFinalPricePlugin
{
    /**
     * @param ObserverInterface $subject
     * @param ObserverInterface $result
     * @param Observer $observer
     *
     * @return ObserverInterface
     */
    public function afterExecute(
        ObserverInterface $subject,
        ObserverInterface $result,
        Observer $observer
    ): ObserverInterface {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $observer->getData('product');

        if ($product->getData(QuoteGenerator::SUBSCRIPTION_FLAG)) {
            $product->setFinalPrice($product->getPrice());
        }

        return $result;
    }
}
