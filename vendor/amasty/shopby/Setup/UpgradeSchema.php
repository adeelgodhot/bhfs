<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Setup;

use Amasty\Shopby\Api\CmsPageRepositoryInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.13.2', '<')) {
            $this->addCmsPageTable($setup);
        }

        if (version_compare($context->getVersion(), '2.1.3', '<')) {
            $this->addIndexForRatingFilter($setup);
        }

        if (version_compare($context->getVersion(), '2.8.0', '<')) {
            $this->addLimitOptionsShowSearchBox($setup);
        }

        if (version_compare($context->getVersion(), '2.8.5', '<')) {
            $this->dropShowOnlyFeatured($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public function addCmsPageTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(CmsPageRepositoryInterface::TABLE);
        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'entity_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('page_id', Table::TYPE_SMALLINT, null, ['nullable' => false])
            ->addColumn('enabled', Table::TYPE_BOOLEAN, null, ['nullable' => false, 'default' => false])
            ->addForeignKey(
                $setup->getFkName(CmsPageRepositoryInterface::TABLE, 'page_id', 'cms_page', 'page_id'),
                'page_id',
                $setup->getTable('cms_page'),
                'page_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addIndexForRatingFilter(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('review_entity_summary');
        $connection = $setup->getConnection();

        $connection->addIndex(
            $table,
            'amasty_shopby_rating_filter',
            ['entity_pk_value', 'entity_type', 'store_id']
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addLimitOptionsShowSearchBox(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_amshopby_filter_setting');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'limit_options_show_search_box',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Show Search Box When Number Options'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function dropShowOnlyFeatured(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropColumn($setup->getTable('amasty_amshopby_filter_setting'), 'show_featured_only');
    }
}
