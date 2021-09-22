<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Cron;

use Amasty\Fpc\Exception\LockException;
use Amasty\Fpc\Model\Config;
use Amasty\Fpc\Model\Queue;
use Psr\Log\LoggerInterface;

class ProcessPageQueue
{
    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Queue $queue,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->queue = $queue;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute()
    {
        if (!$this->config->isModuleEnabled()) {
            return;
        }

        try {
            $this->queue->process();
        } catch (LockException $e) {
            $this->logger->info(__('Can\'t get a file lock for queue processing process $1', $e->getMessage()));
        }
    }
}
