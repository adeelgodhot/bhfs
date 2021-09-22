<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Block\Onepage\Success\Types;

use Amasty\ThankYouPage\Api\ConfigBasicInterface;
use Magento\Cms\Block\Block;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Template;
use Amasty\ThankYouPage\Model\Config\Blocks;

/**
 * Header block
 */
class OrderReview extends Template implements TypesInterface
{
    const NAME_OSC_BLOCK = 'amasty.checkout.success';
    const NAME_CHECKOUT_BLOCK = 'checkout.success';
    const NAME_MULTISHIPPING_BLOCK = 'checkout_success';
    const BLOCK_CONFIG_NAME = 'order_review';

    /**
     * @var ConfigBasicInterface
     */
    private $config;

    /**
     * @var Block
     */
    private $cmsBlock;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var Blocks
     */
    private $blocksConfig;

    public function __construct(
        Template\Context $context,
        ConfigBasicInterface $config,
        Block $cmsBlock,
        Manager $moduleManager,
        Blocks $blocksConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = clone $config;
        $this->cmsBlock = $cmsBlock;
        $this->moduleManager = $moduleManager;
        $this->blocksConfig = $blocksConfig;
        $this->config->setGroupPrefix('block_' . self::BLOCK_CONFIG_NAME);
    }

    /**
     * @return null
     */
    public function getCacheLifetime()
    {
        return null;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        // Use "Amasty_Checkout" success block in case the module is enabled
        $blockName = $this->moduleManager->isEnabled('Amasty_Checkout')
            ? self::NAME_OSC_BLOCK
            : self::NAME_CHECKOUT_BLOCK;

        if (!$this->getLayout()->getBlock(self::NAME_CHECKOUT_BLOCK)
            && !$this->getLayout()->getBlock(self::NAME_OSC_BLOCK)
        ) {
            $blockName = self::NAME_MULTISHIPPING_BLOCK;
        }

        $html = [];

        $width = $this->blocksConfig->getWidthByBlockId(self::BLOCK_CONFIG_NAME);

        $html[] = '<div class="amtyblock-order-review" style="width: ' . $width . '%">';
        $html[] = $this->getLayout()->getBlock($blockName)->toHtml();
        $html[] = '</div>';

        return implode('', array_filter(array_map('trim', $html)));
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->isBlockEnabled();
    }
}
