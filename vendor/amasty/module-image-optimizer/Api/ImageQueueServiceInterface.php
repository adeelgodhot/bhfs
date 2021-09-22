<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizer
 */

declare(strict_types=1);

namespace Amasty\ImageOptimizer\Api;

interface ImageQueueServiceInterface
{
    /**
     * @param \Amasty\ImageOptimizer\Api\Data\QueueInterface $queue
     *
     * @return void
     */
    public function addToQueue(\Amasty\ImageOptimizer\Api\Data\QueueInterface $queue): void;

    /**
     * @param \Amasty\ImageOptimizer\Api\Data\QueueInterface $queue
     *
     * @return void
     */
    public function removeFromQueue(\Amasty\ImageOptimizer\Api\Data\QueueInterface $queue): void;

    /**
     * @param int $limit
     *
     * @return \Amasty\ImageOptimizer\Api\Data\QueueInterface[]
     */
    public function shuffleQueues(int $limit = 10): array;

    /**
     * @return void
     */
    public function clearQueue(): void;

    /**
     * @return bool
     */
    public function isQueueEmpty(): bool;

    /**
     * @return int
     */
    public function getQueueSize(): int;
}
