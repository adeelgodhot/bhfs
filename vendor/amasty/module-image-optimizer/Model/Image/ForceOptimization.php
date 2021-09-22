<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImageOptimizer
 */

declare(strict_types=1);

namespace Amasty\ImageOptimizer\Model\Image;

use Amasty\ImageOptimizer\Model\ImageProcessor;

class ForceOptimization
{
    /**
     * @var \Amasty\ImageOptimizer\Api\ImageQueueServiceInterface
     */
    private $queueService;

    /**
     * @var ImageProcessor
     */
    private $imageProcessor;

    public function __construct(
        \Amasty\ImageOptimizer\Api\ImageQueueServiceInterface $queueService,
        ImageProcessor  $imageProcessor
    ) {
        $this->queueService = $queueService;
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @param int $limit
     *
     * @return void
     */
    public function execute(int $limit): void
    {
        foreach ($this->queueService->shuffleQueues($limit) as $queue) {
            $this->imageProcessor->process($queue);
        }
    }
}
