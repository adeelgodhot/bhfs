<?php

namespace MGS\QuickView\Plugin;

class BlockProductList {

    const XML_PATH_QUICKVIEW_ENABLED = 'mgs_quickview/general/enabled';

    protected $urlInterface;
    protected $scopeConfig;

    public function __construct(
    \Magento\Framework\UrlInterface $urlInterface, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->urlInterface = $urlInterface;
        $this->scopeConfig = $scopeConfig;
    }

    public function aroundGetProductDetailsHtml(
    \Magento\Catalog\Block\Product\ListProduct $subject, \Closure $proceed, \Magento\Catalog\Model\Product $product
    ) {
        $result = $proceed($product);
        $isEnabled = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($isEnabled) {
            $productUrl = $this->urlInterface->getUrl('mgs_quickview/catalog_product/view', array('id' => $product->getId()));
            return $result . '<button data-title="'. __("Quick View") .'" class="mgs-quickview action" data-quickview-url=' . $productUrl . ' href="javascript:void(0);"><span class="pe-7s-search"></span></button>';
        }
        return $result;
    }

}
