<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\FlushesLog;

use Amasty\Base\Model\Serializer;
use Amasty\Fpc\Model\Config;
use Amasty\Fpc\Model\FlushesLogFactory;

class FlushesLogProvider
{
    /**
     * @var FlushesLogFactory
     */
    private $flushesLogFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        FlushesLogFactory $flushesLogFactory,
        Serializer $serializer,
        Config $config
    ) {
        $this->flushesLogFactory = $flushesLogFactory;
        $this->serializer = $serializer;
        $this->config = $config;
    }

    /**
     * @param $mode
     * @param array $tags
     *
     * @return \Amasty\Fpc\Api\Data\FlushesLogInterface|null
     */
    public function getFlushesLogModel($mode, array $tags)
    {
        $trace = [];
        $backtrace = debug_backtrace();
        $flushesLogModel = $this->flushesLogFactory->create();
        if (!$this->isNeedToSave($backtrace)) {
            return null;
        }

        try {
            foreach ($backtrace as $route) {
                $trace[] = [
                    'action' => $route['class'] . $route['type'] . $route['function'] . '()'
                ];
            }

            $now = new \DateTime('now', new \DateTimeZone('utc'));
            $source = $this->getActionSource($backtrace);
            $flushesLogModel->setDate($now->format('Y-m-d H:i:s'));
            $flushesLogModel->setBacktrace($this->serializer->serialize($trace));
            $flushesLogModel->setDetails($this->serializer->serialize($this->getDetails($mode, $tags, $source)));
        } catch (\Throwable $e) {
            null;
        }

        return $flushesLogModel;
    }

    /**
     * @param array $backtrace
     * @return bool
     */
    private function isNeedToSave(array $backtrace): bool
    {
        $needToSave = true;
        $ignoreClasses = $this->config->getIgnoreClasses();

        if (!is_array($ignoreClasses)) {
            return $needToSave;
        }

        foreach ($backtrace as $route) {
            foreach ($ignoreClasses as $class) {
                if (isset($route['class'], $class['class_name'])
                    && strpos($route['class'], $class['class_name']) !== false
                ) {
                    $needToSave = false;
                }
            }
        }

        return $needToSave;
    }

    /**
     * @param array $backtrace
     * @return string
     */
    private function getActionSource(array $backtrace): string
    {
        $source = __('Undefined')->render();

        foreach ($backtrace as $route) {
            if (($route['class'] ?? '') === \Magento\Framework\Console\Cli::class
                && $route['function'] ?? '' === 'doRun'
            ) {
                $source = __('Command Line')->render();
                break;
            }

            if (strpos(($route['class'] ?? ''), 'Adminhtml') !== false) {
                $source = __('Magento Admin')->render();
                break;
            }
        }

        return $source;
    }

    /**
     * @param $mode
     * @param array $tags
     * @param $source
     *
     * @return array
     */
    private function getDetails($mode, array $tags, string $source): array
    {
        return [
            'tags' => !empty($tags) ? implode(',', $tags) : __('No Tags')->render(),
            'mode' => $mode,
            'source' => $source,
        ];
    }
}
