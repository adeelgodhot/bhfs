<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


declare(strict_types=1);

namespace Amasty\RecurringPayments\Block\Adminhtml\System\Config;

use Amasty\RecurringPayments\Model\Config\ConfigurationValidator;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class PaymentsComment extends Field
{
    const WARNING_CHARACTER = ''; // Default Magento warning sign
    const ERROR_MESSAGES = 'error_messages';

    protected $_template = 'config/payment_gateways.phtml';

    /**
     * @var ConfigurationValidator
     */
    private $configurationValidator;

    public function __construct(
        Context $context,
        ConfigurationValidator $configurationValidator,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configurationValidator = $configurationValidator;
    }

    public function render(AbstractElement $element)
    {
        if ($values = $element->getData('values')) {
            if ($issues = $this->getConfigurationIssues($values)) {
                $this->addWarningsToOptions($values, $issues);
                $element->setData('values', $values);
                $this->setData(self::ERROR_MESSAGES, $issues);
            }
        }

        $url = $this->getUrl('adminhtml/system_config/edit/section/payment');
        $element->setComment($this->getCommentMessage($url));

        return $this->toHtml() . parent::render($element);
    }

    public function getErrorMessages(): array
    {
        return $this->getData(self::ERROR_MESSAGES) ?: [];
    }

    private function getCommentMessage($url)
    {
        return __(
            "If your customer adds any subscription product to the cart, only gateway specific payment "
            . "methods will be available at checkout. Make sure selected payment gateways are properly configured "
            . "by going to <a href='%1' target='_blank'>Stores > Configuration > Sales > Payment Methods.</a>",
            $url
        );
    }

    private function getConfigurationIssues(array $options)
    {
        $result = [];

        foreach ($options as $option) {
            $methodCode = $option['value'];
            if ($issues = $this->configurationValidator->getConfigurationIssues($methodCode)) {
                $result[$methodCode] = [
                    'method_name' => $option['label'],
                    'issues'      => $issues
                ];
            }
        }

        return $result;
    }

    private function addWarningsToOptions(array &$options, array $issues)
    {
        foreach ($options as &$option) {
            $methodCode = $option['value'];
            if (isset($issues[$methodCode])) {
                $option['label'] .= ' ️' . self::WARNING_CHARACTER;
                $option['title'] = implode(PHP_EOL, $issues[$methodCode]['issues']);
            }
        }
    }
}
