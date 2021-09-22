<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Block\Email\Items;

class Related extends \Magento\Framework\View\Element\Template
{
    /**
     * @return array
     */
    public function getItems()
    {
        $relatedProducts = [];
        $quote = $this->getData('quote');

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $relatedProducts = array_merge($relatedProducts, $product->getRelatedProducts());
        }

        return $relatedProducts;
    }
}
