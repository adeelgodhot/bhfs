<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Amp
 */


namespace Amasty\Amp\Block\Category\Product\ProductList;

class ToolbarBottom extends \Magento\Framework\View\Element\Template
{
    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        return $this->getLayout()->getBlock('product_list_toolbar')->setIsBottom(true)->toHtml();
    }
}
