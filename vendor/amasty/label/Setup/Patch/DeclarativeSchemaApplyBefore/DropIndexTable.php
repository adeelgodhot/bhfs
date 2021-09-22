<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Setup\Patch\DeclarativeSchemaApplyBefore;

use Amasty\Label\Setup\Uninstall;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * The index table is deleted due to a magento bug, due to which it cannot be transferred to the declarative schema
 * if it refers to a table not specified in the declarative schema
 */
class DropIndexTable implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    public static function getDependencies(): array
    {
        return [
            CheckIsModuleCanProceedUpgradeTo200::class,
            ExtractDataFromAmastyLabelTable::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): DropIndexTable
    {
        $connection = $this->schemaSetup->getConnection();
        $indexTable = $this->schemaSetup->getTable(Uninstall::AMASTY_LABEL_INDEX_TABLE);

        if ($connection->isTableExists($indexTable)) {
            $connection->dropTable($indexTable);
        }

        return $this;
    }
}
