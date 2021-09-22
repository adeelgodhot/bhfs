<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Setup;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetaData;

    /**
     * @var \Amasty\Base\Setup\SerializedFieldDataConverter
     */
    private $fieldDataConverter;

    /**
     * @var Operation\AddAdditionalTemplates
     */
    private $addAdditionalTemplates;

    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
        \Amasty\Base\Setup\SerializedFieldDataConverter $fieldDataConverter,
        \Amasty\Followup\Setup\Operation\AddAdditionalTemplates $addAdditionalTemplates
    ) {
        $this->productMetaData = $productMetaData;
        $this->fieldDataConverter = $fieldDataConverter;
        $this->addAdditionalTemplates = $addAdditionalTemplates;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.1', '<')
            && $this->productMetaData->getVersion() >= "2.2.0"
        ) {
            $table = $setup->getTable('amasty_amfollowup_rule');
            $this->fieldDataConverter->convertSerializedDataToJson($table, 'rule_id', 'conditions_serialized');
        }

        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->addAdditionalTemplates->execute($setup);
        }

        $setup->endSetup();
    }
}
