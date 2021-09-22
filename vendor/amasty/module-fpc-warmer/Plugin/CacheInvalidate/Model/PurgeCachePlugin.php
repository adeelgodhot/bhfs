<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Plugin\CacheInvalidate\Model;

use Amasty\Fpc\Model\FlushesLog\FlushesLogProvider;
use Amasty\Fpc\Model\Queue\RegenerateHandler;
use Amasty\Fpc\Model\Repository\FlushesLogRepository;
use Magento\CacheInvalidate\Model\PurgeCache;

class PurgeCachePlugin
{
    /**
     * @var FlushesLogProvider
     */
    private $flushesLogProvider;

    /**
     * @var FlushesLogRepository
     */
    private $flushesLogRepository;

    /**
     * @var RegenerateHandler
     */
    private $regenerateHandler;

    public function __construct(
        FlushesLogProvider $flushesLogProvider,
        FlushesLogRepository $flushesLogRepository,
        RegenerateHandler $regenerateHandler
    ) {
        $this->flushesLogProvider = $flushesLogProvider;
        $this->flushesLogRepository = $flushesLogRepository;
        $this->regenerateHandler = $regenerateHandler;
    }

    /**
     * @see \Magento\CacheInvalidate\Observer\InvalidateVarnishObserver::execute
     *
     * @param PurgeCache $subject
     * @param \Closure $proceed
     * @param $tagsPattern
     * @return mixed
     */
    public function aroundSendPurgeRequest(PurgeCache $subject, \Closure $proceed, $tagsPattern)
    {
        /**
         * We must log Varnish cache flushes only after succeed flush
         */
        if ($result = $proceed($tagsPattern)) {
            try {
                $mode = \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG . ' Varnish';
                $tags = $this->unpackTags($tagsPattern);
                $flushLogModel = $this->flushesLogProvider->getFlushesLogModel($mode, $tags);
                $this->flushesLogRepository->save($flushLogModel);
                $this->regenerateHandler->execute();
            } catch (\Throwable $e) {
                null;
            }
        }

        return $result;
    }

    /**
     * @param string $tagsPattern
     * @return array
     */
    private function unpackTags(string $tagsPattern): array
    {
        $tags = str_replace(['((^|,)', '(,|$))'], '', $tagsPattern);

        return explode('|', $tags);
    }
}
