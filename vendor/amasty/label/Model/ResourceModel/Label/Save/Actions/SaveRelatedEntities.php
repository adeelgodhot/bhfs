<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\ResourceModel\Label\Save\Actions;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\ResourceModel\Label\Save\AdditionalSaveActionInterface;
use Laminas\Db\Sql\Select;
use Magento\Framework\App\ResourceConnection;

/**
 * @see \Amasty\Label\Model\ResourceModel\Label\Save\Actions\SaveStoreIds
 * @see \Amasty\Label\Model\ResourceModel\Label\Save\Actions\SaveCustomerGroups
 */
class SaveRelatedEntities implements AdditionalSaveActionInterface
{
    /**
     * @var string
     */
    private $mainTable;

    /**
     * @var string
     */
    private $identifierField;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private $labelEntityKey;

    public function __construct(
        ResourceConnection $resourceConnection,
        string $mainTable,
        string $identifierField,
        string $labelEntityKey
    ) {
        $this->mainTable = $mainTable;
        $this->identifierField = $identifierField;
        $this->resourceConnection = $resourceConnection;
        $this->labelEntityKey = $labelEntityKey;
    }

    public function execute(LabelInterface $label): void
    {
        $entitiesIds = $label->getData($this->labelEntityKey);

        if (is_array($entitiesIds)) {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName($this->mainTable);
            $connection->delete($tableName, sprintf(
                '%s = %d',
                LabelInterface::LABEL_ID,
                $label->getLabelId()
            ));

            if (!empty($entitiesIds)) {
                $dataToInsert = array_map(function ($entityId) use ($label): array {
                    return [
                      LabelInterface::LABEL_ID => $label->getLabelId(),
                      $this->identifierField => $entityId
                    ];
                }, $entitiesIds);
                $connection->insertMultiple($tableName, $dataToInsert);
            }
        }
    }
}
