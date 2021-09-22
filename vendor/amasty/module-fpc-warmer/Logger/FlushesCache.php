<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Logger;

use Amasty\Fpc\Model\Config;
use Amasty\Fpc\Model\FlushesLog\FlushesLogProvider;
use Amasty\Fpc\Model\Repository\FlushesLogRepository;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\Cache\FrontendInterface;

class FlushesCache extends TagScope
{
    /**
     * @var FlushesLogRepository
     */
    private $flushesLogRepository;

    /**
     * @var FlushesLogProvider
     */
    private $flushesLogProvider;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        FrontendInterface $frontend,
        FlushesLogRepository $flushesLogRepository,
        FlushesLogProvider $flushesLogProvider,
        Config $config,
        string $tag
    ) {
        parent::__construct($frontend, $tag);
        $this->flushesLogRepository = $flushesLogRepository;
        $this->flushesLogProvider = $flushesLogProvider;
        $this->config = $config;
    }

    /**
     * @param string $mode
     * @param array $tags
     * @return bool
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        $flushesLogModel = $this->flushesLogProvider->getFlushesLogModel($mode, $tags);

        if ($flushesLogModel
            && $this->config->isModuleEnabled()
            && $this->config->isEnableFlushesLog()
        ) {
            try {
                $this->flushesLogRepository->save($flushesLogModel);
            } catch (\Exception $e) {
                null;
            }
        }

        return parent::clean($mode, $tags);
    }
}
