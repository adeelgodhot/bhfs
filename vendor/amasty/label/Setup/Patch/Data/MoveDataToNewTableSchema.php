<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Setup\Patch\Data;

use Amasty\Label\Setup\Model\ConvertFlatLabelDataToStructuredView;
use Amasty\Label\Setup\Patch\DeclarativeSchemaApplyBefore\ExtractDataFromAmastyLabelTable;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\NonTransactionableInterface;

class MoveDataToNewTableSchema implements DataPatchInterface, NonTransactionableInterface
{
    /**
     * @var Iterator
     */
    private $resourceIterator;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ConvertFlatLabelDataToStructuredView
     */
    private $convertFlatLabelDataToStructuredView;

    public function __construct(
        Iterator $resourceIterator,
        ModuleDataSetupInterface $moduleDataSetup,
        ConvertFlatLabelDataToStructuredView $convertFlatLabelDataToStructuredView
    ) {
        $this->resourceIterator = $resourceIterator;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->convertFlatLabelDataToStructuredView = $convertFlatLabelDataToStructuredView;
    }

    public static function getDependencies(): array
    {
        return [
            SetUseInPromoConditionsForPrice::class
        ];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function apply(): MoveDataToNewTableSchema
    {
        $connection = $this->moduleDataSetup->getConnection();
        $tmpTableName = $this->moduleDataSetup->getTable(ExtractDataFromAmastyLabelTable::TEMP_TABLE_NAME);

        if ($connection->isTableExists($tmpTableName)) {
            $select = $connection->select()->from($tmpTableName);
            $connection->beginTransaction();

            try {
                $this->resourceIterator->walk($select, [[$this, 'moveLabelToNewTable']]);
                $connection->commit();
            } catch (\Throwable $e) {
                $connection->rollBack();
                throw $e;
            }

            $connection->dropTable($tmpTableName);
        }

        return $this;
    }

    public function moveLabelToNewTable($args): void
    {
        if (!empty($args['row'])) {
            $labels = $this->convertFlatLabelDataToStructuredView->convert($args['row']);
            $connection = $this->moduleDataSetup->getConnection();

            foreach ($labels as $labelData) {
                foreach ($labelData as $tableName => $tableData) {
                    $tableName = $this->moduleDataSetup->getTable($tableName);

                    if (!empty($tableData)) {
                        $connection->insertOnDuplicate($tableName, $tableData);
                    }
                }
            }
        }
    }
}
