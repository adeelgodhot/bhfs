<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Cron;

use Amasty\Label\Model\Indexer\LabelMainIndexer;
use Amasty\Label\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class InvalidateIsNewLabels
{
    /**
     * @var LabelMainIndexer
     */
    private $indexer;

    /**
     * @var LabelCollectionFactory
     */
    private $labelCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        LabelMainIndexer $indexer,
        LabelCollectionFactory $labelCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->indexer = $indexer;
        $this->labelCollectionFactory = $labelCollectionFactory;
        $this->logger = $logger;
    }

    public function execute(): void
    {
        $labelIds = $this->getIdsForReindex();

        if (!empty($labelIds)) {
            try {
                $this->indexer->executeByLabelIds($labelIds);
            } catch (LocalizedException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @return int[]
     */
    private function getIdsForReindex(): array
    {
        $collection = $this->labelCollectionFactory->create();
        $collection->addActiveFilter();
        $collection->addIsNewFilterApplied();

        return array_map('intval', $collection->getAllIds());
    }
}
