<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Queue;

use Magento\Framework\FlagManager;

class ProcessMetaInfo
{
    const PROCESSING_FLAG = 'amasty_fpc_warmer_processing';
    const QUEUE_TOTAL_FLAG = 'amasty_fpc_queue_total';
    const QUEUE_CRAWLED_FLAG = 'amasty_fpc_queue_crawled';

    /**
     * @var FlagManager
     */
    private $flagManager;

    public function __construct(
        FlagManager $flagManager
    ) {
        $this->flagManager = $flagManager;
    }

    public function addToTotalPagesCrawled(int $incrementValue): void
    {
        $totalPagesCrawled = $this->getTotalPagesCrawled() + $incrementValue;
        $this->saveFlag(self::QUEUE_CRAWLED_FLAG, (string)$totalPagesCrawled);
    }

    public function addToTotalPagesQueued(int $incrementValue): void
    {
        $totalPagesQueued = $this->getTotalPagesQueued() + $incrementValue;
        $this->saveFlag(self::QUEUE_TOTAL_FLAG, (string)$totalPagesQueued);
    }

    public function isQueueLocked(): bool
    {
        return (bool)$this->getFlag(self::PROCESSING_FLAG);
    }

    public function getTotalPagesCrawled(): int
    {
        return (int)$this->getFlag(self::QUEUE_CRAWLED_FLAG);
    }

    public function getTotalPagesQueued(): int
    {
        return (int)$this->getFlag(self::QUEUE_TOTAL_FLAG);
    }

    public function setIsQueueLocked(bool $value): void
    {
        $this->saveFlag(self::PROCESSING_FLAG, (string)$value);
    }

    public function setTotalPagesQueued(int $value): void
    {
        $this->saveFlag(self::QUEUE_TOTAL_FLAG, (string)$value);
    }

    public function resetTotalPagesCrawled(): void
    {
        $this->saveFlag(self::QUEUE_CRAWLED_FLAG, 0);
    }

    protected function getFlag(string $code): string
    {
        return (string)$this->flagManager->getFlagData($code);
    }

    protected function saveFlag(string $code, $value): void
    {
        $this->flagManager->saveFlag($code, (string)$value);
    }
}
