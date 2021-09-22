<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizer
 */

declare(strict_types=1);

namespace Amasty\ImageOptimizer\Model\Queue;

use Amasty\ImageOptimizer\Api\ImageQueueServiceInterface;

class ImageQueueService implements ImageQueueServiceInterface
{
    /**
     * @var ResourceModel\Queue
     */
    private $queueResource;

    /**
     * @var ResourceModel\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Amasty\ImageOptimizer\Model\Queue\ResourceModel\Queue $queueResource,
        \Amasty\ImageOptimizer\Model\Queue\ResourceModel\CollectionFactory $collectionFactory
    ) {
        $this->queueResource = $queueResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function addToQueue(\Amasty\ImageOptimizer\Api\Data\QueueInterface $queue): void
    {
        $this->queueResource->save($queue);
    }

    public function removeFromQueue(\Amasty\ImageOptimizer\Api\Data\QueueInterface $queue): void
    {
        try {
            $this->queueResource->delete($queue);
        } catch (\Exception $e) {
            null;
        }
    }

    public function clearQueue(): void
    {
        $this->queueResource->clear();
    }

    public function shuffleQueues(int $limit = 10): array
    {
        /** @var ResourceModel\Collection $queueCollection */
        $queueCollection = $this->collectionFactory->create();
        $queueCollection->setPageSize((int)$limit);

        $items = $queueCollection->getItems();
        /** @var \Amasty\ImageOptimizer\Api\Data\QueueInterface $queue */
        $ids = [];
        foreach ($items as $queue) {
            $ids[] = $queue->getQueueId();
        }
        if (!empty($ids)) {
            $this->queueResource->deleteByIds($ids);
        }

        return $items;
    }

    public function isQueueEmpty(): bool
    {
        return !(bool)$this->collectionFactory->create()->getSize();
    }

    public function getQueueSize(): int
    {
        return $this->collectionFactory->create()->getSize();
    }
}
