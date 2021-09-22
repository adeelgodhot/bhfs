<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Cron;

use Amasty\Fpc\Cron\Consumer\JobConsumerInterface;

class JobQueueConsumer
{
    /**
     * @var ConsumerAdapter
     */
    private $consumerAdapter;

    public function __construct(ConsumerAdapter $consumerAdapter)
    {
        $this->consumerAdapter = $consumerAdapter;
    }

    public function execute()
    {
        /** @var JobConsumerInterface $jobConsumer */
        foreach ($this->consumerAdapter->all() as $jobConsumer) {
            $jobConsumer->consume();
        }
    }
}
