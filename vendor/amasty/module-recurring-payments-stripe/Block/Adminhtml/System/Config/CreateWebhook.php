<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringStripe
 */


declare(strict_types=1);

namespace Amasty\RecurringStripe\Block\Adminhtml\System\Config;

use Amasty\RecurringStripe\Model\ConfigWebhook;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class CreateWebhook extends Field
{
    /**
     * @var ConfigWebhook
     */
    private $configWebhook;

    /**
     * @var \Amasty\Stripe\Gateway\Config\Config
     */
    private $gatewayConfig;

    public function __construct(
        Context $context,
        ConfigWebhook $configWebhook,
        \Amasty\Stripe\Gateway\Config\Config $gatewayConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configWebhook = $configWebhook;
        $this->gatewayConfig = $gatewayConfig;
    }

    public function render(AbstractElement $element)
    {
        if (!$this->isStripeApiConfigured()) {
            $element->setComment(
                __(
                    '<strong>Important</strong>: Webhook Secret can be generated only after you fill in Publishable Key'
                    . ' and Secret Key and save these settings by pressing the "Save Config" button.'
                )
            );
        }

        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        /** @var Template $block */
        $block = $this->_layout->createBlock(Template::class);
        $block->setTemplate('Amasty_RecurringStripe::config/create.phtml')
            ->setData('input_html', parent::_getElementHtml($element));

        if (!$this->configWebhook->getWebhookSecret() && $this->isStripeApiConfigured()) {
            $button = $this->getLayout()->createBlock(
                Button::class
            )->setData(
                [
                    'id'    => 'create_button',
                    'label' => __('Create'),
                ]
            )->setDataAttribute(
                ['role' => 'amrecurring-create-button']
            );

            $block->setChild('button', $button);
        }

        return $block->toHtml();
    }

    public function isStripeApiConfigured(): bool
    {
        return $this->gatewayConfig->isActive() && !empty($this->gatewayConfig->getPublicKey());
    }
}
