<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2020 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_Core
 */

namespace EH\Core\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use EH\Core\Model\Processor;
use EH\Core\Model\ConfigReader;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Component\ComponentRegistrar;

/**
 * Class Heading
 * @package EH\Core\Block\Adminhtml\System\Config\Form
 */
class Heading extends Field
{
    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var ConfigReader
     */
    protected $configReader;

    /**
     * Heading constructor.
     * @param Processor $processor
     * @param ConfigReader $configReader
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Processor $processor,
        ConfigReader $configReader,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->processor = $processor;
        $this->configReader = $configReader;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(AbstractElement $element)
    {
        $extensionName = $element->getLegend()->getText();
        $extensionVersion = $this->processor->getComposerVersion($extensionName, ComponentRegistrar::MODULE);
        $extensionDetails = $this->processor->getExtensionVersion($extensionName);
        $html = '<div class="eh-heading">
            <div class="row-1">
                <span class="logo">
                    <img src="'.$this->configReader->getGeneralConfig()->getLogoLink().'">
                </span>
                <a type="button" class="action- scalable action-secondary" data-ui-id="view-extensions-button" target="_blank"
                    href="'.$this->configReader->getGeneralConfig()->getExtensionsLink().'">
                    <span>'.__("View More Extensions").'</span>
                </a>
            </div>';

        if(isset($extensionDetails['label'])) {
            $html .= '
                <div class="content row-2">' .
                    __(
                        '%1 <span>v%2</span> is developed by <a href="%3" target="_blank">%4</a>.',
                        $extensionDetails['label'],
                        $extensionVersion,
                        $this->configReader->getGeneralConfig()->getSiteLink(),
                        $this->configReader->getGeneralConfig()->getSiteName()
                    ) .' '.
                    __(
                        '<a href="%1" target="_blank">Need help?</a>',
                        $this->configReader->getGeneralConfig()->getSupportLink()
                    ). '
                </div>';
        }

        $html.='</div>';

        if(isset($extensionDetails['update_needed']) && $extensionDetails['update_needed']) {
            $html .= '<div class="eh-update-notification">
                        '.__("New version").'
                        '.__($extensionDetails['status_message']).'
                        '.__($extensionDetails['notification_msg']).'
                    </div>';
        }

        return $html;
    }
}
