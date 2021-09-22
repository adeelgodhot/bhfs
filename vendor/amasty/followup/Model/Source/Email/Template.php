<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Source\Email;

class Template extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    protected $_emailConfig;

    /**
     * @var \Magento\Email\Model\ResourceModel\Template\CollectionFactory
     */
    protected $_templatesFactory;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory
     * @param \Magento\Email\Model\Template\Config $emailConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Email\Model\ResourceModel\Template\CollectionFactory $templatesFactory,
        \Magento\Email\Model\Template\Config $emailConfig,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_coreRegistry = $coreRegistry;
        $this->_templatesFactory = $templatesFactory;
        $this->_emailConfig = $emailConfig;
    }

    public function toOptionArray()
    {
        if (!($collection = $this->_coreRegistry->registry('config_system_email_template'))) {
            $collection = $this->_templatesFactory->create();
            $collection->load();
            $this->_coreRegistry->register('config_system_email_template', $collection);
        }

        $options = [];
        foreach ($collection as $model) {
            if ($model->getOrigTemplateCode() == 'amfollowup_emails_header_template'
                || $model->getOrigTemplateCode() == 'amfollowup_emails_footer_template'
                || $model->getOrigTemplateCode() == 'amfollowup_emails_header_template_modern'
                || $model->getOrigTemplateCode() == 'amfollowup_emails_footer_template_modern'
            ) {
                $options[] = [
                    'value' => $model->getTemplateId(),
                    'label' => $model->getTemplateCode()
                ];
            }
        }

        $templateId = str_replace('/', '_', $this->getPath());
        $templateLabel = $this->_emailConfig->getTemplateLabel($templateId);
        $templateLabelDefault = __('%1 (Default)', $templateLabel);

        array_unshift(
            $options,
            [
                'value' => $templateId,
                'label' => $templateLabelDefault
            ]
        );

        array_unshift(
            $options,
            [
                'value' => $templateId . '_modern',
                'label' => __('%1 Modern (Default)', $templateLabel)
            ]
        );

        return $options;
    }

}