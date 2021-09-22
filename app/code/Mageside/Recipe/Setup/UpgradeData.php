<?php

namespace Mageside\Recipe\Setup;

use \Magento\Framework\Setup\UpgradeDataInterface;
use \Magento\Framework\Setup\ModuleContextInterface;
use \Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class UpgradeData
 * @package BodenkoVV\AskQuestion\Setup
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.22') < 0) {
            $this->copyColumnDateOptionsTranslation(
                $setup,
                'ms_recipe_filter_options_varchar',
                'ms_recipe_filter_options',
                'label'
            );
            $this->copyColumnDateFilterTranslation(
                $setup,
                'ms_recipe_filter_varchar',
                'ms_recipe_filter',
                'type'
            );
            $this->copyColumnDateTranslation(
                $setup,
                'ms_recipe_varchar',
                'ms_recipe',
                'title'
            );
            $this->copyColumnDateTranslation(
                $setup,
                'ms_recipe_text',
                'ms_recipe',
                'ingredients'
            );
            $this->copyColumnDateTranslation(
                $setup,
                'ms_recipe_text',
                'ms_recipe',
                'short_description'
            );
            $this->copyColumnDateTranslation(
                $setup,
                'ms_recipe_text',
                'ms_recipe',
                'method'
            );
        }

        $setup->endSetup();
    }

    /**
     * @param $setup
     * @param $toTableName
     * @param $fromTableName
     * @param $fromColumn
     */
    public function copyColumnDateTranslation($setup, $toTableName, $fromTableName, $fromColumn)
    {
        $fromTable = $setup->getTable($fromTableName);
        $storeTable = $setup->getTable('ms_recipe_store');
        $toTable = $setup->getTable($toTableName);
        $storeId = 0;

        $select = $setup->getConnection()->select()
            ->from($fromTable, ['recipe_id', $fromColumn.' as meta_value'])
            ->join($storeTable,
                $fromTable.'.recipe_id='.$storeTable.'.recipe_id',
                ['store_id']
            );

        $query = $setup->getConnection()
            ->insertFromSelect(
                $select,
                $toTable,
                ['recipe_id','meta_value','store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
        $setup->getConnection()->query($query);

        $setup
            ->getConnection()
            ->update($toTable, ['meta_key' => $fromColumn , 'store_id' => $storeId], 'meta_key IS NULL');
        $setup
            ->getConnection()
            ->dropColumn(
                $fromTable,
                $fromColumn
            );
    }

    /**
     * @param $setup
     * @param $toTableName
     * @param $fromTableName
     * @param $fromColumn
     */
    public function copyColumnDateFilterTranslation($setup, $toTableName, $fromTableName, $fromColumn)
    {
        $fromTable = $setup->getTable($fromTableName);
        $toTable =  $setup->getTable($toTableName);
        $storeId = 0;

        $select = $setup->getConnection()
            ->select()
            ->from($fromTable, ['id as filter_id', $fromColumn.' as meta_value']);
        $query = $setup->getConnection()
            ->insertFromSelect(
                $select,
                $toTable,
                ['filter_id','meta_value'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
        $setup->getConnection()->query($query);

        $setup
            ->getConnection()
            ->update($toTable, ['meta_key' => $fromColumn, 'store_id' => $storeId], 'meta_key IS NULL');
        $setup
            ->getConnection()
            ->dropColumn(
                $fromTable,
                $fromColumn
            );
    }

    /**
     * @param $setup
     * @param $toTableName
     * @param $fromTableName
     * @param $fromColumn
     */
    public function copyColumnDateOptionsTranslation($setup, $toTableName, $fromTableName, $fromColumn)
    {
        $fromTable = $setup->getTable($fromTableName);
        $toTable = $setup->getTable($toTableName);
        $storeId = 0;

        $select = $setup->getConnection()
            ->select()
            ->from($fromTable, ['id as filter_id', $fromColumn.' as meta_value']);
        $query = $setup->getConnection()
            ->insertFromSelect(
                $select,
                $toTable,
                ['filter_option_id','meta_value'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            );
        $setup->getConnection()->query($query);

        $setup
            ->getConnection()
            ->update($toTable, ['meta_key' => $fromColumn, 'store_id' => $storeId], 'meta_key IS NULL');
        $setup
            ->getConnection()
            ->dropColumn(
                $fromTable,
                $fromColumn
            );
    }
}
