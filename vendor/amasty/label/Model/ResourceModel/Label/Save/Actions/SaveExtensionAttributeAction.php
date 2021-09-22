<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\ResourceModel\Label\Save\Actions;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\Label\Parts\MetaProvider;
use Amasty\Label\Model\ResourceModel\Label\Save\AdditionalSaveActionInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;

class SaveExtensionAttributeAction implements AdditionalSaveActionInterface
{
    /**
     * @var MetaProvider
     */
    private $metaProvider;

    /**
     * @var string
     */
    private $labelPartCode;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        MetaProvider $metaProvider,
        ResourceConnection $resourceConnection,
        string $labelPartCode
    ) {
        $this->metaProvider = $metaProvider;
        $this->labelPartCode = $labelPartCode;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(LabelInterface $label): void
    {
        $getter = $this->metaProvider->getGetter($this->labelPartCode);
        $attributeModel = $label->getExtensionAttributes()->{$getter}();

        if ($attributeModel instanceof DataObject) {
            $data = $attributeModel->getData();
            $data[LabelInterface::LABEL_ID] = $label->getLabelId();
            $connection = $this->resourceConnection->getConnection();
            $data = array_intersect_key($data, array_flip($this->getTableFields()));
            $connection->insertOnDuplicate(
                $this->resourceConnection->getTableName($this->metaProvider->getTable($this->labelPartCode)),
                $data
            );
        }
    }

    /**
     * @return string[]
     */
    private function getTableFields(): array
    {
        $tableDescription = $this->resourceConnection->getConnection()->describeTable(
            $this->resourceConnection->getTableName($this->metaProvider->getTable($this->labelPartCode))
        );

        return array_keys($tableDescription);
    }
}
