<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Observer;

use Magento\Framework\Event\Observer;

class CreateQuoteItem implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $quoteItem = $observer->getQuoteItem();

        $quoteItem->setIsFromWishlist($product->getIsFromWishlist());
    }
}
