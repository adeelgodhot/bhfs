<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Block\Onepage\Success\Types;

use Amasty\ThankYouPage\Api\ConfigCrosssellInterface;
use Amasty\ThankYouPage\Block\Onepage\Success\Types\Crosssell\Items;
use Amasty\ThankYouPage\Model\Config\Blocks;
use Magento\Catalog\Block\Product\ProductList\Item\AddTo\Compare;
use Magento\Catalog\Block\Product\ProductList\Item\Container;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Crosssell block
 */
class Crosssell extends Template implements TypesInterface
{
    const BLOCK_CONFIG_NAME = 'crosssell';

    /**
     * @var ConfigCrosssellInterface
     */
    private $config;

    /**
     * @var Blocks
     */
    private $blocksConfig;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function __construct(
        Context $context,
        ConfigCrosssellInterface $config,
        Blocks $blocksConfig,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = clone $config;
        $this->blocksConfig = $blocksConfig;
        $this->config->setGroupPrefix('block_' . self::BLOCK_CONFIG_NAME);
        $this->objectManager = $objectManager;
    }

    /**
     * @return null
     */
    public function getCacheLifetime()
    {
        return null;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isBlockEnabled();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $this->generateLayout();

        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getItemsHtml()
    {
        $html = [];

        $width = $this->blocksConfig->getWidthByBlockId('cross_sell');
        $markUp = $this->blocksConfig->isMarkupEnabled();
        $class = $markUp ? '<div class="amtyblock-cross-sell" style="width: ' . $width . '%">' : '';

        $html[] = $class;
        $html[] = $this->_layout->getBlock('checkout.cart.crosssell')->toHtml();
        $html[] = $markUp ? '</div>' : '';

        return implode('', array_filter(array_map('trim', $html)));
    }

    /**
     * The idea is to mimic part of src/vendor/magento/module-checkout/view/frontend/layout/checkout_cart_index.xml
     *
     * <block class="Magento\Checkout\Block\Cart\Crosssell" name="checkout.cart.crosssell"
     * template="Magento_Catalog::product/list/items.phtml" after="-" ifconfig="checkout/cart/crosssell_enabled">
     *  <arguments>
     *      <argument name="type" xsi:type="string">crosssell</argument>
     *  </arguments>
     *
     *  <block class="Magento\Catalog\Block\Product\ProductList\Item\Container" name="crosssell.product.addto"
     *  as="addto">
     *  <block class="Magento\Catalog\Block\Product\ProductList\Item\AddTo\Compare"
     *      name="crosssell.product.addto.compare" as="compare"
     *      template="Magento_Catalog::product/list/addto/compare.phtml"/>
     *  </block>
     * </block>
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function generateLayout()
    {
        $layout = $this->getLayout();
        $blockArguments = [
            'template' => 'Magento_Catalog::product/list/items.phtml',
            'type' => 'crosssell'
        ];
        $viewModelBlock = \Magento\Catalog\ViewModel\Product\Listing\PreparePostData::class;
        if (class_exists($viewModelBlock)) {
            $blockArguments['view_model'] = $this->objectManager->get($viewModelBlock);
        }
        $block = $layout->createBlock(
            Items::class,
            'checkout.cart.crosssell',
            [
                'data' => $blockArguments,
            ]
        );

        $block->setProductLimit($this->config->getProductLimit())
            ->setShowOutOfStock($this->config->isShowOutOfStock());

        $layout->createBlock(
            Container::class,
            'crosssell.product.addto'
        );

        $layout->createBlock(
            Compare::class,
            'crosssell.product.addto.compare',
            ['template' => 'Magento_Catalog::product/list/addto/compare.phtml']
        );

        $layout->setChild('checkout.cart.crosssell', 'crosssell.product.addto', 'addto');
        $layout->setChild('checkout.cart.crosssell', 'crosssell.product.addto.compare', 'compare');

        return $this;
    }
}
