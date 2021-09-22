<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Mpanel\Observer;

use Magento\Framework\Event\Observer;

use Magento\Framework\Event\ObserverInterface;

class RemoveBlock implements ObserverInterface {

    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer) {

        /** @var \Magento\Framework\View\Layout $layout */

        $layout = $observer->getLayout();

        $rmv_compare_sidebar = $this->_scopeConfig->getValue('mpanel/sidebar_config/compare_sidebar', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $rmv_wishlist_sidebar = $this->_scopeConfig->getValue('mpanel/sidebar_config/wishlist_sidebar', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $rmv_reorder_sidebar = $this->_scopeConfig->getValue('mpanel/sidebar_config/reorder_sidebar', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $rmv_custom_block_sidebar = $this->_scopeConfig->getValue('mpanel/sidebar_config/custom_block_sidebar', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($rmv_compare_sidebar) { $layout->unsetElement('catalog.compare.sidebar'); }
        if ($rmv_wishlist_sidebar) { $layout->unsetElement('wishlist_sidebar'); }
        if ($rmv_reorder_sidebar) { $layout->unsetElement('sale.reorder.sidebar'); }
        if ($rmv_custom_block_sidebar) { $layout->unsetElement('custom.sidebar.content'); }
        
        $block = $layout->getBlock('product.detail.info');
        
        
        $filterBlock = $layout->getBlock('catalog.leftnav');
        if($filterBlock) {
            $rmv_filter = $this->_scopeConfig->getValue('mpanel/catalog/layernavigation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            if ($rmv_filter) { $layout->unsetElement('catalog.leftnav'); }
        }
        
        if($block) {
            $rmv_review = $this->_scopeConfig->getValue('mpanel/product_details/sku', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $rmv_sku = $this->_scopeConfig->getValue('mpanel/product_details/reviews_summary', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $rmv_wishlist_bt = $this->_scopeConfig->getValue('mpanel/product_details/wishlist', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $rmv_compare_bt = $this->_scopeConfig->getValue('mpanel/product_details/compare', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $rmv_short_des = $this->_scopeConfig->getValue('mpanel/product_details/short_description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $rmv_short_upsell = $this->_scopeConfig->getValue('mpanel/product_details/upsell_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            
            if (!$rmv_review) { $layout->unsetElement('product.info.review'); }
            if (!$rmv_sku) { $layout->unsetElement('product.info.sku'); }
            if (!$rmv_compare_bt) { $layout->unsetElement('view.addto.compare'); }
            if (!$rmv_wishlist_bt) { $layout->unsetElement('view.addto.wishlist'); }
            if (!$rmv_short_des) { $layout->unsetElement('product.info.overview'); }
            if (!$rmv_short_upsell) { $layout->unsetElement('product.info.upsell'); }
        }
    }
}
