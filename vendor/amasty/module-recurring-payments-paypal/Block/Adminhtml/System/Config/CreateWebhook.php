<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPaypal
 */


namespace Amasty\RecurringPaypal\Block\Adminhtml\System\Config;

use Amasty\RecurringPaypal\Model\ConfigProvider;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;

class CreateWebhook extends Field
{
    const PRIMARY_ELEMENT = 'primary_element';

    protected $_template = 'Amasty_RecurringPaypal::config/createWebhook.phtml';

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Context $context,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    public function getPrimaryElement(): AbstractElement
    {
        return $this->getData(self::PRIMARY_ELEMENT);
    }

    public function isPaypalApiConfigured(): bool
    {
        return !empty($this->configProvider->getPaypalCredentials());
    }

    public function render(AbstractElement $element)
    {
        if (!$this->isPaypalApiConfigured()) {
            $element->setComment(
                __('<strong>Important</strong>: Webhook Secret can be generated only after you fill in Client ID and'
                    . ' Client Secret and save these settings by pressing the "Save Config" button.')
            );
            $element->setData('disabled', 'disabled');
        }

        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $this->setData(self::PRIMARY_ELEMENT, $element);

        return $element->getElementHtml() . $this->_toHtml();
    }
}
