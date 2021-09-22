<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ThankYouPage
 */

namespace Amasty\ThankYouPage\Block\Adminhtml\System\Config\Field;

use Amasty\ThankYouPage\Model\Config\Blocks;
use Magento\Backend\Block\Template\Context;
use Amasty\ThankYouPage\Model\Config;

/**
 * Block for ordering sorting component in Admin System Configuration
 */
class BlocksOrder extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @const string
     */
    const ELEMENT_TEMPLATE = 'Amasty_ThankYouPage::system/config/field/blocks_order.phtml';

    /**
     * @var Blocks
     */
    private $blocksConfig;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Context $context,
        Blocks $blocksConfig,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->blocksConfig = $blocksConfig;
        $this->config = $config;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $blocks = $this->blocksConfig->getSortedBlocksByConfigValue($element->getEscapedValue());

        $templateBlock = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Template::class,
            'amthankyoublocks_sort_order',
            [
                'data' => [
                    'template' => self::ELEMENT_TEMPLATE,
                ],
            ]
        );

        $form = $this->getForm();

        if ($this->config->isMarkupEnabled($form->getScopeCode(), $form->getScope())) {
            $templateBlock->setData('markup', 1);
        }

        return $templateBlock->setBlocks($blocks)
            ->setElementName($element->getName())
            ->toHtml();
    }
}
