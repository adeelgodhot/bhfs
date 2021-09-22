<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Block\Onepage\Success;

use Amasty\ThankYouPage\Block\Onepage\Success\Types\CreateAccount;
use Amasty\ThankYouPage\Block\Onepage\Success\Types\TypesInterface;
use Amasty\ThankYouPage\Model\Config\Blocks;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;
use Amasty\ThankYouPage\Model\Config;

class Facade extends AbstractBlock
{
    /**
     * @var Blocks
     */
    private $blocksConfig;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Session
     */
    private $checkoutSession;

    public function __construct(
        Context $context,
        Blocks $blocksConfig,
        Config $config,
        Session $checkoutSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->blocksConfig = $blocksConfig;
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Collect HTML from children blocks
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = [];
        $wrap = [];
        $class = $this->getClassForBlock();
        $isMultishipping = $this->checkoutSession->getCheckoutState() == 'multishipping_success';

        foreach ($this->getBlockInstances() as $block) {
            if ($block->isEnabled()) {
                if (!$isMultishipping) {
                    $html[] = $block->toHtml();
                } elseif ($isMultishipping && !$block instanceof CreateAccount) {
                    $html[] = $block->toHtml();
                }
            }
        }

        $wrap[] = '<div ' . $class . '>';
        $wrap[] = implode('<hr />', array_filter(array_map('trim', $html)));
        $wrap[] = '</div>';

        return implode('', $wrap);
    }

    /**
     * Instantiate blocks
     *
     * @return TypesInterface[]
     */
    private function getBlockInstances()
    {
        $instances = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        foreach ($this->blocksConfig->getSortedBlocks() as $blockConfig) {
            $blockInstance = $objectManager->create($blockConfig['class_name']);
            if (!empty($blockConfig['template'])) {
                $blockInstance->setTemplate($blockConfig['template']);
            }

            $instances[] = $blockInstance;
        }

        return $instances;
    }

    /**
     * @return string
     */
    private function getClassForBlock(): string
    {
        if ($this->config->isMarkupEnabled()) {
            if ($this->config->isForceOneColumnMobileViewEnabled()) {

                return 'class="amtypage-main-container -stretched-blocks"';
            }

            return 'class="amtypage-main-container"';
        }

        return '';
    }
}
