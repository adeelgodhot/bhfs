<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


declare(strict_types=1);

namespace Amasty\Fpc\Model\Queue;

use Amasty\Fpc\Cron\Consumer\GenerateQueue;
use Amasty\Fpc\Model\BackgroundJob\Repository;
use Amasty\Fpc\Model\Config;
use Amasty\Fpc\Model\Debug\ContextDebugService;
use Amasty\Fpc\Model\Queue;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

class RegenerateHandler
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var Repository
     */
    private $jobRepository;

    /**
     * @var ContextDebugService
     */
    private $contextDebugService;

    /**
     * @var bool
     */
    private $isQueueGenerated = false;

    public function __construct(
        Config $config,
        Queue $queue,
        State $appState,
        Repository $jobRepository,
        ContextDebugService $contextDebugService = null
    ) {
        $this->config = $config;
        $this->queue = $queue;
        $this->appState = $appState;
        $this->jobRepository = $jobRepository;
        $this->contextDebugService = $contextDebugService ?:
            ObjectManager::getInstance()->get(ContextDebugService::class);
    }

    /**
     * Regenerate pages queue if it possible or create background job to regenerate queue via cron
     *
     * @param bool|null $immediate
     * @return array
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(bool $immediate = null): array
    {
        $result = true;
        $processedItems = 0;
        $immediate = $immediate ?? !$this->config->isRegenerateQueueInBackground()
            && $this->config->getQueueAfterGenerate();

        if (!$this->isAllowedToRegenerate()) {
            return [$result, $processedItems];
        }

        if ($immediate) {
            try {
                $this->appState->setAreaCode(Area::AREA_GLOBAL);
            } catch (\Exception $e) {
                null;
                //launched from admin
                //(emulateArea not working due the area emulation in \Amasty\Fpc\Model\Source\PageType\Emulated)
            }

            $this->queue->forceUnlock();

            if (!$this->isQueueGenerated) {
                $this->contextDebugService->flush();
                list($result, $processedItems) = $this->queue->generate();
                $this->isQueueGenerated = $result;
            }
        } elseif ($this->config->isRegenerateQueueInBackground()) {
            $regenerateJob = $this->jobRepository->getEmptyJob();
            $regenerateJob->setJobCode(GenerateQueue::JOB_CODE);
            $this->jobRepository->save($regenerateJob);
        }

        return [$result, $processedItems];
    }

    /**
     * Queue regeneration is not allowed in frontend area to prevent performance issues on frontend
     */
    private function isAllowedToRegenerate(): bool
    {
        return $this->config->isModuleEnabled()
            && !$this->isFrontendArea();
    }

    private function isFrontendArea(): bool
    {
        $isFrontend = false;

        try {
            $isFrontend = $this->appState->getAreaCode() == Area::AREA_FRONTEND;
        } catch (LocalizedException $e) {
            null;
        }

        return $isFrontend;
    }
}
