<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Fpc
 */


namespace Amasty\Fpc\Model;

use Amasty\Fpc\Api\QueuePageRepositoryInterface;
use Amasty\Fpc\Exception\LockException;
use Amasty\Fpc\Model\Crawler\Crawler;
use Amasty\Fpc\Model\Queue\ProcessMetaInfo;
use Amasty\Fpc\Model\Source\PagesProvider;

class Queue
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ResourceModel\Queue\Page\CollectionFactory
     */
    private $pageCollectionFactory;

    /**
     * @var \Amasty\Fpc\Model\QueuePageRepository
     */
    private $pageRepository;

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var PagesProvider
     */
    private $pagesProvider;

    /**
     * @var ProcessMetaInfo
     */
    private $processMetaInfo;

    public function __construct(
        Config $config,
        ResourceModel\Queue\Page\CollectionFactory $pageCollectionFactory,
        QueuePageRepositoryInterface $pageRepository,
        Crawler $crawler,
        PagesProvider $pagesProvider,
        ProcessMetaInfo $processMetaInfo
    ) {
        $this->config = $config;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->pageRepository = $pageRepository;
        $this->crawler = $crawler;
        $this->pagesProvider = $pagesProvider;
        $this->processMetaInfo = $processMetaInfo;
    }

    protected function lock()
    {
        if ($this->processMetaInfo->isQueueLocked()) {
            throw new LockException(__('Another lock detected (the Warmer queue is in a progress).'));
        }

        $this->processMetaInfo->setIsQueueLocked(true);
    }

    protected function unlock()
    {
        $this->processMetaInfo->setIsQueueLocked(false);
    }

    public function forceUnlock()
    {
        $this->processMetaInfo->setIsQueueLocked(false);
    }

    public function generate(): array
    {
        $this->lock();
        $processedItems = 0;
        $queueLimit = $this->config->getQueueLimit();
        $sourceType = $this->config->getSourceType();
        $sourcePages = $this->pagesProvider->getSourcePages($sourceType, $queueLimit);

        if (empty($sourcePages)) {
            $this->unlock();

            return [false, $processedItems];
        }

        try {
            $this->pageRepository->clear();
        } catch (\Exception $e) {
            $this->unlock();

            return [false, $processedItems];
        }

        foreach ($sourcePages as $page) {
            $this->pageRepository->addPage($page);
            $processedItems++;

            if (!$this->processMetaInfo->isQueueLocked()) {
                return [false, $processedItems];
            }
        }

        $this->unlock();
        $this->processMetaInfo->setTotalPagesQueued($processedItems);
        $this->processMetaInfo->resetTotalPagesCrawled();

        return [true, $processedItems];
    }

    public function process(): int
    {
        $this->lock();
        $uncachedPagesCollection = $this->getUncachedPages();
        $this->crawler->processPages($uncachedPagesCollection);
        $this->processMetaInfo->addToTotalPagesCrawled($uncachedPagesCollection->count());
        $this->unlock();

        return $uncachedPagesCollection->count();
    }

    private function getUncachedPages()
    {
        /** @var ResourceModel\Queue\Page\Collection $pageCollection */
        $pageCollection = $this->pageCollectionFactory->create()->setOrder('rate');
        $pageCollection->setPageSize($this->config->getBatchSize());

        return $pageCollection;
    }
}
