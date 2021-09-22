<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Plugin\Setup;

class UpgradeData
{
    /**
     * @var \Amasty\Base\Setup\SerializedFieldDataConverter
     */
    private $fieldDataConverter;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $setup;

    public function __construct(
        \Amasty\Base\Setup\SerializedFieldDataConverter $fieldDataConverter,
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup
    )
    {
        $this->fieldDataConverter = $fieldDataConverter;
        $this->setup = $setup;
    }

    /**
     * @param \Magento\SalesRule\Setup\UpgradeData $subject
     * @param $result
     * @return mixed
     */
    public function afterConvertSerializedDataToJson(\Magento\SalesRule\Setup\UpgradeData $subject, $result)
    {
        $this->fieldDataConverter->convertSerializedDataToJson(
            $this->setup->getTable('amasty_amfollowup_rule'),
            'rule_id',
            'conditions_serialized'
        );

        return $result;
    }
}
