<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\ResourceModel\Label\Grid;

use Amasty\Label\Api\Data\LabelFrontendSettingsInterface;
use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\Label;
use Amasty\Label\Model\Label\Parts\FrontendSettings;
use Amasty\Label\Model\ResourceModel\Label as LabelResource;
use Amasty\Label\Model\ResourceModel\Label\Collection as LabelsCollection;
use Amasty\Label\Setup\Uninstall;
use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    const PRODUCT_PREFIX = 'product';
    const CATEGORY_PREFIX = 'category';

    protected function _construct(): void
    {
        $this->_init(Label::class, LabelResource::class);
        $this->setMainTable(LabelResource::TABLE_NAME);
        $this->addFilterToMap(LabelInterface::LABEL_ID, 'main_table.' . LabelInterface::LABEL_ID);
        $this->addFilterToMap(
            'category_position',
            self::CATEGORY_PREFIX . '.' . LabelFrontendSettingsInterface::POSITION
        );
        $this->addFilterToMap(
            'product_position',
            self::PRODUCT_PREFIX . '.' . LabelFrontendSettingsInterface::POSITION
        );
    }

    public function loadOnlyJoinedParts(): void
    {
        if (!$this->isLoaded()) {
            $this->getSelect()->reset(Select::COLUMNS);
        }
    }

    protected function _beforeLoad()
    {
        $this->joinCatalogParts();

        return parent::_beforeLoad();
    }

    protected function getCatalogPartColumns(): array
    {
        $columnsDescribe = $this->getConnection()->describeTable($this->getTable(
            Uninstall::AMASTY_LABEL_CATALOG_PARTS_TABLE
        ));

        return array_reduce(array_keys($columnsDescribe), function (array $carry, string $columnName):array {
            if ($columnName !== LabelFrontendSettingsInterface::TYPE && $columnName !== LabelInterface::LABEL_ID) {
                $carry[] = $columnName;
            }

            return $carry;
        }, []);
    }

    private function joinCatalogParts(): void
    {
        $select = $this->getSelect();
        $tableName = $this->getTable(Uninstall::AMASTY_LABEL_CATALOG_PARTS_TABLE);
        $columns = $this->getCatalogPartColumns();
        $mods = [
            self::CATEGORY_PREFIX => LabelsCollection::MODE_LIST,
            self::PRODUCT_PREFIX => LabelsCollection::MODE_PDP
        ];

        foreach ($mods as $modName => $modCode) {
            $joinColumns = [];

            foreach ($columns as $columnName) {
                $joinColumns["{$modName}_{$columnName}"] = $columnName;
            }

            $select->join(
                [$modName => $tableName],
                sprintf(
                    'main_table.%1$s = %2$s.%1$s and %2$s.%3$s = %4$d',
                    LabelInterface::LABEL_ID,
                    $modName,
                    FrontendSettings::TYPE,
                    $modCode
                ),
                $joinColumns
            );
        }
    }
}
