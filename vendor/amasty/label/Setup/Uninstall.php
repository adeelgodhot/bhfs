<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Setup;

use Amasty\Label\Model\ResourceModel\Label;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    const FLAT_LABEL_TABLE = 'am_label';
    const AMASTY_LABEL_STORE_TABLE = 'amasty_label_store';
    const AMASTY_LABEL_CUSTOMER_GROUP_TABLE = 'amasty_label_customer_group';
    const AMASTY_LABEL_CATALOG_PARTS_TABLE = 'amasty_label_catalog_parts';
    const AMASTY_LABEL_INDEX_TABLE = 'amasty_label_index';
    const AMASTY_LABEL_TOOLTIP_TABLE = 'amasty_label_tooltip';

    const MODULE_TABLES = [
        self::AMASTY_LABEL_STORE_TABLE,
        self::AMASTY_LABEL_CUSTOMER_GROUP_TABLE,
        self::AMASTY_LABEL_CATALOG_PARTS_TABLE,
        self::AMASTY_LABEL_INDEX_TABLE,
        self::AMASTY_LABEL_TOOLTIP_TABLE,
        Label::TABLE_NAME
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        foreach (self::MODULE_TABLES as $table) {
            $tableName = $setup->getTable($table);

            if ($connection->isTableExists($tableName)) {
                $connection->dropTable($tableName);
            }
        }

        $setup->endSetup();
    }
}
