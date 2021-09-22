<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeTo123
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->changeSalesRuleColumn($setup);
        $this->dropForeignKeyForSalesRule($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function changeSalesRuleColumn(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('amasty_amfollowup_schedule');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'sales_rule_id',
            [
                'NULLABLE' => true,
                'UNSIGNED' => true,
                'TYPE' => Table::TYPE_INTEGER,
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function dropForeignKeyForSalesRule(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('amasty_amfollowup_history');
        $setup->getConnection()->dropForeignKey(
            $tableName,
            $setup->getFkName('amasty_amfollowup_history', 'sales_rule_id', 'salesrule', 'rule_id')
        );
    }
}
