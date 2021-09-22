<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Block\Email\Items;

class Upsell extends \Magento\Framework\View\Element\Template
{
    /**
     * @return array
     */
    public function getItems()
    {
        $upSellProducts = [];
        $quote = $this->getData('quote');

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $upSellProducts = array_merge($upSellProducts, $product->getUpSellProducts());
        }

        return $upSellProducts;
    }
}
