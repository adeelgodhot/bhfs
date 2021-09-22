<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\UpgradeTo123
     */
    private $upgrade123;

    public function __construct(
        Operation\UpgradeTo123 $upgrade123
    ) {
        $this->upgrade123 = $upgrade123;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @since 1.1.0 Attribute Relation functional release */
        if ($context->getVersion() && version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->changeRuleSegmentsColumn($setup);
        }

        if ($context->getVersion() && version_compare($context->getVersion(), '1.2.3', '<')) {
            $this->upgrade123->execute($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function changeRuleSegmentsColumn(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('amasty_amfollowup_rule');
        if ($setup->getConnection()->tableColumnExists($tableName, 'rule_id')) {
            $setup->getConnection()->changeColumn(
                $tableName,
                'segments',
                'segments',
                [
                    'NULLABLE' => true,
                    'TYPE'     => Table::TYPE_TEXT,
                ]
            );
        }
    }
}
