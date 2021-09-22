<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Cron;

use Amasty\Fpc\Cron\Consumer\JobConsumerInterface;

class ConsumerAdapter
{
    /**
     * @var JobConsumerInterface[]
     */
    private $consumers;

    public function __construct(array $consumers = [])
    {
        foreach ($consumers as $consumer) {
            if (!($consumer instanceof JobConsumerInterface)) {
                throw new \LogicException(
                    sprintf('Job consumer must implement %s', JobConsumerInterface::class)
                );
            }
        }

        $this->consumers = $consumers;
    }

    public function get(string $jobCode): JobConsumerInterface
    {
        if (!isset($this->consumers[$jobCode])) {
            throw new \RuntimeException("Consumer for job code '{$jobCode}' is not defined");
        }

        return $this->consumers[$jobCode];
    }

    public function all(): array
    {
        return $this->consumers;
    }
}
