<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\ResourceModel;

use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Model\Indexer\IndexBuilder;
use Amasty\Label\Model\ResourceModel\Label\CollectionFactory;
use Amasty\Label\Setup\Uninstall;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\Store;

class GetLabelsByProductIdAndStoreId
{
    /**
     * @var CollectionFactory
     */
    private $labelsCollectionFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        CollectionFactory $labelsCollectionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->labelsCollectionFactory = $labelsCollectionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(int $productId, int $storeId, int $mode): array
    {
        $labelCollection = $this->labelsCollectionFactory->create();
        $connection = $labelCollection->getConnection();
        $labelCollection->addActiveFilter();
        $select = $labelCollection->getSelect();
        $select->joinInner(
            ['ali' => $this->resourceConnection->getTableName(Uninstall::AMASTY_LABEL_INDEX_TABLE)],
            sprintf(
                'main_table.%1$s = ali.%1$s',
                LabelInterface::LABEL_ID
            ),
            []
        );
        $labelCollection->setMode($mode);
        $select->where($connection->prepareSqlCondition(IndexBuilder::PRODUCT_ID, ['eq' => $productId]));
        $select->where($connection->prepareSqlCondition(
            IndexBuilder::STORE_ID,
            ['in' => [$storeId, Store::DEFAULT_STORE_ID]]
        ));
        /** @var LabelInterface[] $labels **/
        $labels = $labelCollection->getItems();

        return $this->resortByPriority($labels);
    }

    /**
     * @param LabelInterface[] $labels
     * @return LabelInterface[]
     */
    private function resortByPriority(array $labels): array
    {
        usort($labels, function (LabelInterface $labelA, LabelInterface $labelB) {
            return $labelA->getPriority() <=> $labelB->getPriority();
        });

        return $labels;
    }
}
